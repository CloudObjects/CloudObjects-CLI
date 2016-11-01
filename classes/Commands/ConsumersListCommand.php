<?php

namespace CloudObjects\CLI\Commands;

use Symfony\Component\Console\Input\InputInterface, Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Cilex\Command\Command;
use CloudObjects\CLI\NotAuthorizedException;
use CloudObjects\SDK\COIDParser;

class ConsumersListCommand extends Command {

  protected function configure() {
    $this->setName('domain-consumers:list')
      ->setDescription('List all the domains that are associated consumers of a domain along with their roles.')
      ->addArgument('hostname', InputArgument::REQUIRED, 'The hostname of the domain.');
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $app = $this->getContainer();
    if (!isset($app['context'])) throw new NotAuthorizedException();

    if (!preg_match(COIDParser::REGEX_HOSTNAME, $input->getArgument('hostname'))) {
      $output->writeln('<error>Invalid hostname: '.$input->getArgument('hostname').'</error>');
      return;
    }

    $consumersResponse = json_decode($app['context']->getClient()
      ->get('/dr/'.$input->getArgument('hostname').'/consumers')
      ->getBody(), true);

    foreach ($consumersResponse['consumers'] as $key => $value) {
      $output->writeln($key."\t\t".$value);
    }
  }

}
