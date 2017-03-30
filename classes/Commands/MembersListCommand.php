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

class MembersListCommand extends Command {

  protected function configure() {
    $this->setName('domain-members:list')
      ->setDescription('List all the accounts that are members of a domain along with their roles.')
      ->addArgument('hostname', InputArgument::REQUIRED, 'The hostname of the domain.');
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $app = $this->getContainer();
    if (!isset($app['context'])) throw new NotAuthorizedException();

    if (!preg_match(COIDParser::REGEX_HOSTNAME, $input->getArgument('hostname'))) {
      $output->writeln('<error>Invalid hostname: '.$input->getArgument('hostname').'</error>');
      return;
    }

    $membersResponse = json_decode($app['context']->getClient()
      ->get('/dr/'.$input->getArgument('hostname').'/members')
      ->getBody(), true);

    foreach ($membersResponse['members'] as $key => $value) {
      $output->writeln("aauid:".$key."\t\t".$value);
    }
  }

}
