<?php

/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/. */
 
namespace CloudObjects\CLI\Commands;

use Symfony\Component\Console\Input\InputInterface, Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Cilex\Command\Command;
use CloudObjects\CLI\NotAuthorizedException, CloudObjects\CLI\UpdateChecker;
use CloudObjects\SDK\COIDParser;

class ProvidersSecretGetCommand extends Command {

  protected function configure() {
    $this->setName('domain-providers:secret')
      ->setAliases(array('secret', 'domain-consumers:secret'))
      ->setDescription('Get the shared secret between two namespaces. The namespaces do not have to be associated.')
      ->addArgument('coid1', InputArgument::REQUIRED, 'The COID of the first namespace or any object within. You must be a member of this namespace.')
      ->addArgument('coid2', InputArgument::OPTIONAL, 'The COID of the second namespace or any object within. You do not need to be a member of this namespace.', 'cloudobjects.io');
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $app = $this->getContainer();
    if (!isset($app['context'])) throw new NotAuthorizedException();

    $coid1 = COIDParser::fromString($input->getArgument('coid1'));
    $coid2 = COIDParser::fromString($input->getArgument('coid2'));

    if (COIDParser::getType($coid1)==COIDParser::COID_INVALID) {
      $output->writeln('<error>Invalid COID: '.(string)$coid1.'</error>');
      return;
    }
    if (COIDParser::getType($coid2)==COIDParser::COID_INVALID) {
      $output->writeln('<error>Invalid COID: '.(string)$coid2.'</error>');
      return;
    }

    $secretResponse = json_decode($app['context']->getClient()
      ->get('/dr/'.$coid1->getHost().'/providers/'.$coid2->getHost())
      ->getBody(), true);

    
    $output->writeln($secretResponse['shared_secret']);
    UpdateChecker::execute($app, $output);
  }

}
