<?php

/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/. */
 
namespace CloudObjects\CLI\Commands;

use Symfony\Component\Console\Input\InputInterface, Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Cilex\Command\Command;
use CloudObjects\CLI\NotAuthorizedException, CloudObjects\CLI\UpdateChecker;

class ConfigurationJobCreateCommand extends Command {

  protected function configure() {
    $this->setName('configuration-job:create')
      ->setAliases(array('confjob'))
      ->setDescription('Creates a new configuration job in CloudObjects.')
      ->addArgument('filename', InputArgument::OPTIONAL, 'The name of the file containing the configuration data.');
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $app = $this->getContainer();
    if (!isset($app['context'])) throw new NotAuthorizedException();

    if ($filename = $input->getArgument('filename')) {
      if (file_exists($filename)) {
        $content = file_get_contents($filename);
      } else throw new \Exception("The specified file does not exist.");
    } else throw new \Exception("No filename!");
    // TODO: If no file is specified, data is read from standard input.

    $app['context']->getClient()->post('/ws/configurationJob',
      [ 'body' => $content ]);
    $output->writeln('Configuration job has been created.');
    UpdateChecker::execute($app, $output);
  }

}
