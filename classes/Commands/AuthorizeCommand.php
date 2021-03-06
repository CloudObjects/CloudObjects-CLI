<?php

/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/. */
 
namespace CloudObjects\CLI\Commands;

use Exception;
use Symfony\Component\Console\Input\InputInterface, Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use CloudObjects\CLI\CredentialManager;
use GuzzleHttp\Client;

class AuthorizeCommand extends Command {

    protected function configure() {
        $this->setName('authorize')
            ->setDescription("Authorize this system's current user with an AAUID and authorization code.")
            ->addArgument('aauid', InputArgument::REQUIRED, 'The AAUID of the account.')
            ->addArgument('code', InputArgument::REQUIRED, 'A valid authorization code for the account.');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $aauid = $input->getArgument('aauid');
        try {
            $client = new Client([
                'base_uri' => 'https://'.$aauid.'.aauid.net/'
            ]);
      
            $accessTokenResponse = json_decode($client->post('/cli/token', [
                'form_params' => [
                    'code' => $input->getArgument('code'),
                    'version' => $this->getApplication()->getVersion()
                ]
            ])->getBody(), true);
            if (isset($accessTokenResponse['access_token'])) {
                CredentialManager::setAuthorizedWithCredentials($this->getApplication(),
                    $aauid, $accessTokenResponse['access_token']);
                $output->writeln('<info>You are authorized now!</info>');
            } else
                throw new Exception();

            return Command::SUCCESS;

        } catch (Exception $e) {
            $output->writeln('<error>You could not be authorized!</error>');
            $output->writeln('Please get a new authorization code from this website: https://cloudobjects.io/clitool');

            return Command::FAILURE;
        }
    }

}
