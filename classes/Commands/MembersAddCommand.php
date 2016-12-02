<?php

namespace CloudObjects\CLI\Commands;

use Symfony\Component\Console\Input\InputInterface, Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Cilex\Command\Command;
use CloudObjects\CLI\NotAuthorizedException;
use CloudObjects\SDK\COIDParser, CloudObjects\SDK\AccountGateway\AAUIDParser;

class MembersAddCommand extends Command {

  protected function configure() {
    $this->setName('domain-members:add')
      ->setDescription('Adds an account as a member to a domain or updates the role.')
      ->addArgument('hostname', InputArgument::REQUIRED, 'The hostname of the domain.')
      ->addArgument('aauid', InputArgument::REQUIRED, 'The AAUID of the account.')
      ->addArgument('role', InputArgument::REQUIRED, 'The COID of the role object.');
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

    $role = COIDParser::fromString($input->getArgument('role'));
    if (COIDParser::getType($role)==COIDParser::COID_INVALID) {
      $output->writeln('<error>Invalid role COID: '.(string)$role.'</error>');
      return;
    }

    $app['context']->getClient()
      ->put('/dr/'.$input->getArgument('hostname').'/members/'.AAUIDParser::getAAUID($aauid), [
        'form_params' => [ 'role' => (string)$role ]
      ]);
  }

}
