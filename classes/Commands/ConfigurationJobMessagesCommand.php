<?php

/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/. */
 
namespace CloudObjects\CLI\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use CloudObjects\CLI\CredentialManager, CloudObjects\CLI\NotAuthorizedException,
    CloudObjects\CLI\UpdateChecker;

class ConfigurationJobMessagesCommand extends Command {

    protected function configure() {
        $this->setName('configuration-job:messages')
            ->setAliases(array('messages', 'log'))
            ->setDescription('Fetch and read all messages from the configuration job log.');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {    
        if (CredentialManager::getContext() === null)
            throw new NotAuthorizedException;

        $logResponse = json_decode(CredentialManager::getContext()->getClient()->delete('/ws/messages')->getBody(), true);
        if (isset($logResponse['messages']) && count($logResponse['messages'])==0) {
            $output->writeln('There are no messages available.');
        } elseif (isset($logResponse['messages'])) {
            foreach ($logResponse['messages'] as $m) {
                switch ($m['type']) {
                    case "SUCCESS":
                        $output->writeln('<info>SUCCESS</info>: '.$m['content']);
                        break;
                    case "FAILURE":
                        $output->writeln('<error>FAILURE</error>: '.$m['content']);
                        break;
                    default:
                        $output->writeln($m['type'].': '.$m['content']);
                }
                $output->writeln('['.$m['created_at'].']');
                $output->writeln('');
            }
        } else {
            throw new \Exception("Unexpected response from server.");
        }

        UpdateChecker::execute($this->getApplication(), $output);
        return Command::SUCCESS;
    }

}
