<?php

/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/. */
 
namespace CloudObjects\CLI\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use CloudObjects\CLI\CredentialManager;

class DeauthorizeCommand extends Command {

    protected function configure() {
        $this->setName('deauthorize')
            ->setDescription("Deauthorize this system's current user.");
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        CredentialManager::setUnauthorized();
        $output->writeln('<info>You are unauthorized now!</info>');
        return Command::SUCCESS;
    }

}
