<?php

/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/. */
 
namespace CloudObjects\CLI\Commands;

use Symfony\Component\Console\Input\InputInterface, Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use GuzzleHttp\Exception\BadResponseException;
use CloudObjects\CLI\CredentialManager, CloudObjects\CLI\NotAuthorizedException,
    CloudObjects\CLI\UpdateChecker;
use CloudObjects\SDK\COIDParser;

class AttachmentDeleteCommand extends Command {

    protected function configure() {
        $this->setName('attachment:delete')
            ->setDescription('Deletes an object attachment.')
            ->addArgument('coid', InputArgument::REQUIRED, 'The COID of the object.')
            ->addArgument('filename', InputArgument::REQUIRED, 'The filename for the attachment.');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        if (CredentialManager::getContext() === null)
            throw new NotAuthorizedException;

        $coid = COIDParser::fromString($input->getArgument('coid'));

        if (COIDParser::getType($coid)==COIDParser::COID_INVALID) {
            $output->writeln('<error>Invalid COID: '.(string)$coid.'</error>');
            return Command::FAILURE;
        }

        $filename = $input->getArgument('filename');

        try {
            CredentialManager::getContext()->getClient()->delete('/ws/'.$coid->getHost().$coid->getPath().'/'
                .basename($filename), [ 'body' => ' ' ]);

            UpdateChecker::execute($this->getApplication(), $output);
            return Command::SUCCESS;
        } catch (BadResponseException $e) {
            $jsonBody = json_decode($e->getResponse()->getBody(), true);
            $output->writeln('<error>'.(isset($jsonBody['error']) ? $jsonBody['error'] : $e->getMessage()).'</error>');
            
            UpdateChecker::execute($this->getApplication(), $output);
            return Command::FAILURE;
        }        
    }

}
