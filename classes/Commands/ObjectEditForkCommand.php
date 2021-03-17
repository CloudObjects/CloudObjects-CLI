<?php

/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/. */
 
namespace CloudObjects\CLI\Commands;

use Exception;
use Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Cilex\Command\Command;
use CloudObjects\CLI\NotAuthorizedException, CloudObjects\CLI\UpdateChecker;
use CloudObjects\SDK\COIDParser;

class ObjectEditForkCommand extends Command {

    use ConfigurationJobResultFormatterTrait;

    protected function configure() {
        $this->setName('object:edit-or-fork')
            ->setAliases([ 'ef' ])
            ->setDescription('Retrieves the description of an object, opens it in an editor and creates a configuration job based on the result.')
            ->addArgument('coid', InputArgument::REQUIRED, 'The COID of the object.')
            ->addOption('format', null, InputOption::VALUE_OPTIONAL, 'Format for the object description.', 'xml');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $app = $this->getContainer();
        if (!isset($app['context']))
            throw new NotAuthorizedException;

        $editor = getenv('EDITOR');
        if ($editor === false || trim($editor) == '')
            throw new Exception("Missing EDITOR environment variable.");

        $coid = COIDParser::fromString($input->getArgument('coid'));

        if (COIDParser::getType($coid)==COIDParser::COID_INVALID) {
            $output->writeln('<error>Invalid COID: '.(string)$coid.'</error>');
            return;
        }

        switch ($input->getOption('format')) {
            case "xml":
                $mimeType = 'application/rdf+xml';
                break;
            case "turtle":
                $mimeType = 'text/turtle';
                break;
            case "triples":
                $mimeType = 'text/plain';
                break;
            case "jsonraw":
                $mimeType = 'application/json';
                break;
            case "jsonld":
                $mimeType = 'application/ld+json';
                break;
            default:
                throw new Exception('Unsupported format!');
        }

        $objectResponse = $app['context']->getClient()
            ->get('/ws/'.$coid->getHost().$coid->getPath().'/raw', [
                'headers' => ['Accept' => $mimeType]
            ]);

        // Store object temporarily
        $filename = sys_get_temp_dir().DIRECTORY_SEPARATOR.uniqid()
            .'.'.$input->getOption('format');
        file_put_contents($filename, (string)$objectResponse->getBody());
        $timestamp = filemtime($filename);

        // Run default editor
        passthru($editor.' '.$filename.' > `tty`');

        // Create configuration job if file was modified
        clearstatcache();
        if ($timestamp != filemtime($filename)) {
            $result = json_decode($app['context']->getClient()->post('/ws/configurationJobSync',
                [ 'body' => file_get_contents($filename) ])->getBody(), true);
            $this->printResponse($result, $output);
        } else {
            $output->writeln("No changes to object.");
        }

        unlink($filename);    

        UpdateChecker::execute($app, $output);
    }

}
