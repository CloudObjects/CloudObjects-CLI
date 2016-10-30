<?php

namespace CloudObjects\CLI\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Cilex\Command\Command;
use CloudObjects\CLI\CredentialManager;

class DeauthorizeCommand extends Command {

  protected function configure() {
    $this->setName('deauthorize')
      ->setDescription("Deauthorize this system's current user.");
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    CredentialManager::setUnauthorized();
    $output->writeln('<info>You are unauthorized now!</info>');
  }

}
