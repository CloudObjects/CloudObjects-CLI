<?php

/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/. */
 
namespace CloudObjects\CLI\Commands;

use Symfony\Component\Console\Input\InputInterface, Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Cilex\Command\Command;
use CloudObjects\CLI\NotAuthorizedException;
use CloudObjects\SDK\COIDParser;

class ProvidersRemoveCommand extends Command {

  protected function configure() {
    $this->setName('domain-providers:remove')
      ->setDescription('Removes the provider association of two domains.')
      ->addArgument('hostname', InputArgument::REQUIRED, 'The hostname of the first domain.')
      ->addArgument('hostname2', InputArgument::REQUIRED, 'The hostname of the second domain.');
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $app = $this->getContainer();
    if (!isset($app['context'])) throw new NotAuthorizedException();

    if (!preg_match(COIDParser::REGEX_HOSTNAME, $input->getArgument('hostname'))) {
      $output->writeln('<error>Invalid hostname: '.$input->getArgument('hostname').'</error>');
      return;
    }

    if (!preg_match(COIDParser::REGEX_HOSTNAME, $input->getArgument('hostname2'))) {
      $output->writeln('<error>Invalid hostname: '.$input->getArgument('hostname2').'</error>');
      return;
    }

    $app['context']->getClient()
      ->delete('/dr/'.$input->getArgument('hostname').'/providers/'.$input->getArgument('hostname2'));
  }

}
