<?php

namespace CloudObjects\CLI\Commands;

use Symfony\Component\Console\Input\InputInterface, Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Cilex\Command\Command;
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
      $app = $this->getContainer();
      $accessTokenResponse = json_decode($client->post('/cli/token', [
          'form_params' => [
            'code' => $input->getArgument('code'),
            'version' => $app['console.version']
          ]
        ])->getBody(), true);
      if (isset($accessTokenResponse['access_token'])) {
        CredentialManager::setAuthorizedWithCredentials($this->getContainer(),
          $aauid, $accessTokenResponse['access_token']);
        $output->writeln('<info>You are authorized now!</info>');
      } else throw new \Exception();

      return 0;

    } catch (\Exception $e) {
      $output->writeln('<error>You could not be authorized!</error>');
      $output->writeln('Please get a new authorization code from this website: https://cloudobjects.io/clitool');

      return -1;
    }
  }

}
