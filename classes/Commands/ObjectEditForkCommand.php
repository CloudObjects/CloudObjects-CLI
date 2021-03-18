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
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use CloudObjects\CLI\CredentialManager,
    CloudObjects\CLI\NotAuthorizedException, CloudObjects\CLI\UpdateChecker;
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
        if (CredentialManager::getContext() === null)
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

        $objectResponse = CredentialManager::getContext()->getClient()
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
            $response = json_decode(CredentialManager::getContext()->getClient()->post('/ws/configurationJobSync',
                [ 'body' => file_get_contents($filename) ])->getBody(), true);
            $this->printResponse($response, $output);

            $helper = $this->getHelper('question');
            $question = new ConfirmationQuestion('Do you want to reopen the editor (y/n)?', false);
            
            // On failure, ask user to retry
            while ($response['result'] == 'failure' && $helper->ask($input, $output, $question)) {
                // Repeat previous steps until successful
                passthru($editor.' '.$filename.' > `tty`');

                clearstatcache();
                if ($timestamp != filemtime($filename)) {
                    $response = json_decode(CredentialManager::getContext()->getClient()->post('/ws/configurationJobSync',
                        [ 'body' => file_get_contents($filename) ])->getBody(), true);
                    $this->printResponse($response, $output);
                } else {
                    $output->writeln("No changes to object.");
                    break;        
                }
            }            
        } else {
            $output->writeln("No changes to object.");
        }

        // Remove temporary file
        unlink($filename);    

        UpdateChecker::execute($this->getApplication(), $output);
        return Command::SUCCESS;
    }

}
