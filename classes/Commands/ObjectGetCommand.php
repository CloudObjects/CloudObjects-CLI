<?php

/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/. */
 
namespace CloudObjects\CLI\Commands;

use Exception;
use Symfony\Component\Console\Input\InputInterface, Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use CloudObjects\SDK\COIDParser;
use CloudObjects\CLI\CredentialManager, CloudObjects\CLI\NotAuthorizedException,
    CloudObjects\CLI\UpdateChecker;

class ObjectGetCommand extends Command {

    protected function configure() {
        $this->setName('object:get')
            ->setAliases(array('get'))
            ->setDescription('Get the description of an object.')
            ->addArgument('coid', InputArgument::REQUIRED, 'The COID of the object.')
            ->addOption('raw', null, InputOption::VALUE_NONE, 'Returns raw object description without implicit/enhanced triples.')
            ->addOption('format', null, InputOption::VALUE_OPTIONAL, 'Format for the object description.', 'xml');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {        
        if (CredentialManager::getContext() === null)
            throw new NotAuthorizedException;

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
            ->get('/ws/'.$coid->getHost().$coid->getPath().'/'
                . ($input->getOption('raw') ? 'raw' : 'object'), [
                    'headers' => ['Accept' => $mimeType]
                ]);

        $output->writeln((string)$objectResponse->getBody());
        UpdateChecker::execute($this->getApplication(), $output);

        return Command::SUCCESS;
    }

}
