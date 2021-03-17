<?php

/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/. */
 
namespace CloudObjects\CLI\Commands;

use Symfony\Component\Console\Input\InputInterface, Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use CloudObjects\CLI\CredentialManager, CloudObjects\CLI\NotAuthorizedException;
use CloudObjects\SDK\COIDParser;

class ConsumersAddCommand extends Command {

    protected function configure() {
        $this->setName('domain-consumers:add')
            ->setDescription('Associates the second domain as a consumer of the first domain or updates the role.')
            ->addArgument('hostname', InputArgument::REQUIRED, 'The hostname of the first domain.')
            ->addArgument('hostname2', InputArgument::REQUIRED, 'The hostname of the second domain.')
            ->addArgument('role', InputArgument::REQUIRED, 'The COID of the role object.');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {    
        if (CredentialManager::getContext() === null)
            throw new NotAuthorizedException;

        if (!preg_match(COIDParser::REGEX_HOSTNAME, $input->getArgument('hostname'))) {
            $output->writeln('<error>Invalid hostname: '.$input->getArgument('hostname').'</error>');
            return Command::FAILURE;
        }

        if (!preg_match(COIDParser::REGEX_HOSTNAME, $input->getArgument('hostname2'))) {
            $output->writeln('<error>Invalid hostname: '.$input->getArgument('hostname2').'</error>');
            return Command::FAILURE;
        }

        $role = COIDParser::fromString($input->getArgument('role'));
        if (COIDParser::getType($role) == COIDParser::COID_INVALID) {
            $output->writeln('<error>Invalid role COID: '.(string)$role.'</error>');
            return Command::FAILURE;
        }

        CredentialManager::getContext()->getClient()
            ->put('/dr/'.$input->getArgument('hostname').'/consumers/'.$input->getArgument('hostname2'), [
                'form_params' => [ 'role' => (string)$role ]
            ]);
        return Command::SUCCESS;
    }

}
