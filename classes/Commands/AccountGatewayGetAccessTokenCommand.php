<?php

namespace CloudObjects\CLI\Commands;

use Symfony\Component\Console\Input\InputInterface, Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Cilex\Command\Command;
use ML\IRI\IRI;
use CloudObjects\CLI\NotAuthorizedException;
use CloudObjects\SDK\COIDParser;
use CloudObjects\SDK\AccountGateway\AAUIDParser;

class AccountGatewayGetAccessTokenCommand extends Command {

  protected function configure() {
    $this->setName('account-gateway:get-token')
      ->setDescription('Get an access token for an account for the specified service or application.')
      ->addArgument('coid', InputArgument::REQUIRED, 'The COID of the accessor.')
      ->addArgument('aauid', InputArgument::REQUIRED, 'The AAUID of the account.');
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $app = $this->getContainer();
    if (!isset($app['context'])) throw new NotAuthorizedException();

    $coid = COIDParser::fromString($input->getArgument('coid'));

    if (COIDParser::getType($coid)==COIDParser::COID_INVALID) {
      $output->writeln('<error>Invalid COID: '.(string)$coid.'</error>');
      return;
    }

    $aauid = AAUIDParser::fromString($input->getArgument('aauid'));
    if (AAUIDParser::getType($aauid)!=AAUIDParser::AAUID_ACCOUNT) {
      $output->writeln('<error>Invalid AAUID: '.(string)$aauid.'</error>');
      return;
    }

    $objectResponse = json_decode($app['context']->getClient()
      ->get('/ws/'.$coid->getHost().$coid->getPath()
        .'/accessToken:'.AAUIDParser::getAAUID($aauid))
      ->getBody(), true);

    $output->writeln($objectResponse['access_token']);
  }

}
