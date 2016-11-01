<?php

namespace CloudObjects\CLI\Commands;

use Symfony\Component\Console\Input\InputInterface, Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Cilex\Command\Command;
use CloudObjects\CLI\NotAuthorizedException;
use CloudObjects\SDK\COIDParser, CloudObjects\SDK\AccountGateway\AAUIDParser;

class ProvidersRemoveCommand extends Command {

  protected function configure() {
    $this->setName('domain-providers:remove')
      ->setDescription('Removes the provider association of two domains.')
      ->addArgument('hostname', InputArgument::REQUIRED, 'The hostname of the domain.')
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

    $providersResponse = json_decode($app['context']->getClient()
      ->delete('/dr/'.$input->getArgument('hostname').'/providers/'.$input->getArgument('hostname2'))
      ->getBody(), true);
  }

}
