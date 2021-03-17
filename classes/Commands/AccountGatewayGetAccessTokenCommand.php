<?php

/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/. */
 
namespace CloudObjects\CLI\Commands;

use Symfony\Component\Console\Input\InputInterface, Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use CloudObjects\CLI\CredentialManager, CloudObjects\CLI\NotAuthorizedException;
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
    
    if (CredentialManager::getContext() === null) throw new NotAuthorizedException();

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

    $objectResponse = json_decode(CredentialManager::getContext()->getClient()
      ->get('/ws/'.$coid->getHost().$coid->getPath()
        .'/accessToken:'.AAUIDParser::getAAUID($aauid))
      ->getBody(), true);

    $output->writeln($objectResponse['access_token']);
  }

}
