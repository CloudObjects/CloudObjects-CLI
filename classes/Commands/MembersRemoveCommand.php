<?php

/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/. */
 
namespace CloudObjects\CLI\Commands;

use Symfony\Component\Console\Input\InputInterface, Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Cilex\Command\Command;
use CloudObjects\CLI\NotAuthorizedException;
use CloudObjects\SDK\COIDParser, CloudObjects\SDK\AccountGateway\AAUIDParser;

class MembersRemoveCommand extends Command {

  protected function configure() {
    $this->setName('domain-members:remove')
      ->setDescription('Removes an account as a member of a domain.')
      ->addArgument('hostname', InputArgument::REQUIRED, 'The hostname of the domain.')
      ->addArgument('aauid', InputArgument::REQUIRED, 'The AAUID of the account.');
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $app = $this->getContainer();
    if (!isset($app['context'])) throw new NotAuthorizedException();

    if (!preg_match(COIDParser::REGEX_HOSTNAME, $input->getArgument('hostname'))) {
      $output->writeln('<error>Invalid hostname: '.$input->getArgument('hostname').'</error>');
      return;
    }

    $aauid = AAUIDParser::fromString($input->getArgument('aauid'));
    if (AAUIDParser::getType($aauid)!=AAUIDParser::AAUID_ACCOUNT) {
      $output->writeln('<error>Invalid AAUID: '.(string)$aauid.'</error>');
      return;
    }

    $app['context']->getClient()
      ->delete('/dr/'.$input->getArgument('hostname').'/members/'.AAUIDParser::getAAUID($aauid));
  }

}
