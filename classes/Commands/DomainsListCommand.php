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

class DomainsListCommand extends Command {

  protected function configure() {
    $this->setName('domains:list')
      ->setDescription('List all the domains that the currently authorized account has access to.');
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $app = $this->getContainer();
    if (!isset($app['context'])) throw new NotAuthorizedException();

    $domainsResponse = json_decode($app['context']->getClient()
      ->get('/dr/')->getBody(), true);

      foreach ($domainsResponse['domains'] as $domain) {
        $output->writeln($domain);
      }
  }

}
