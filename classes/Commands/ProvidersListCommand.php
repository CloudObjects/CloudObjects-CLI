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

class ProvidersListCommand extends Command {

  protected function configure() {
    $this->setName('domain-providers:list')
      ->setDescription('List all the domains that are associated providers of a domain along with their roles.')
      ->addArgument('hostname', InputArgument::REQUIRED, 'The hostname of the domain.');
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $app = $this->getContainer();
    if (!isset($app['context'])) throw new NotAuthorizedException();

    if (!preg_match(COIDParser::REGEX_HOSTNAME, $input->getArgument('hostname'))) {
      $output->writeln('<error>Invalid hostname: '.$input->getArgument('hostname').'</error>');
      return;
    }

    $providersResponse = json_decode($app['context']->getClient()
      ->get('/dr/'.$input->getArgument('hostname').'/providers')
      ->getBody(), true);

    foreach ($providersResponse['providers'] as $key => $value) {
      $output->writeln($key."\t\t".$value);
    }
  }

}
