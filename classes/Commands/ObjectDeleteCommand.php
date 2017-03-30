<?php

/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/. */
 
namespace CloudObjects\CLI\Commands;

use Symfony\Component\Console\Input\InputInterface, Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Cilex\Command\Command;
use GuzzleHttp\Exception\BadResponseException;
use CloudObjects\CLI\NotAuthorizedException, CloudObjects\CLI\UpdateChecker;
use CloudObjects\SDK\COIDParser;

class ObjectDeleteCommand extends Command {

  protected function configure() {
    $this->setName('object:delete')
      ->setDescription('Deletes an object.')
      ->addArgument('coid', InputArgument::REQUIRED, 'The COID of the object.');
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $app = $this->getContainer();
    if (!isset($app['context'])) throw new NotAuthorizedException();

    $coid = COIDParser::fromString($input->getArgument('coid'));

    if (COIDParser::getType($coid)==COIDParser::COID_INVALID) {
      $output->writeln('<error>Invalid COID: '.(string)$coid.'</error>');
      return -1;
    }
    if (COIDParser::getType($coid)==COIDParser::COID_ROOT) {
      $output->writeln('<error>Root objects (namespaces) cannot be deleted with this command.</error>');
      return -2;
    }

    try {
      $app['context']->getClient()->delete('/ws/'.$coid->getHost().$coid->getPath()
        .'/object', [ 'body' => ' ' ]);
      return 0;
    } catch (BadResponseException $e) {
      $jsonBody = json_decode($e->getResponse()->getBody(), true);
      $output->writeln('<error>'.(isset($jsonBody['error']) ? $jsonBody['error'] : $e->getMessage()).'</error>');
      return -3;
    }

    UpdateChecker::execute($app, $output);
  }

}
