<?php

namespace CloudObjects\CLI\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Cilex\Command\Command;
use CloudObjects\CLI\NotAuthorizedException;

class AccountCommand extends Command {

  protected function configure() {
    $this->setName('account')
      ->setDescription('Get information about the currently authorized account.');
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $app = $this->getContainer();
    if (!isset($app['context'])) throw new NotAuthorizedException();

    $output->writeln("Account:\t\t".$app['context']->getAAUID());
  }

}
