<?php

namespace CloudObjects\CLI\Commands;

use Symfony\Component\Console\Input\InputInterface, Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Cilex\Command\Command;
use CloudObjects\CLI\NotAuthorizedException, CloudObjects\CLI\CredentialManager;
use CloudObjects\SDK\AccountGateway\AAUIDParser;
use GuzzleHttp\Client;

class SelfUpdateCommand extends Command {

  protected function configure() {
    $this->setName('self-update')
      ->setDescription("Updates this application to the latest version.");
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $app = $this->getContainer();
    if (!isset($app['context'])) throw new NotAuthorizedException();

    $client = new Client([
      'base_uri' => 'https://'.AAUIDParser::getAAUID($app['context']->getAAUID()).'.aauid.net/'
    ]);

    // Check for update
    $updateCheckResponse = json_decode($client->get('/cli/update'
      . '?previous_access_token='.$app['context']->getAccessToken())
      ->getBody(), true);

    if (isset($updateCheckResponse['source_url'])
        && isset($updateCheckResponse['code'])) {

      // Set filenames
      $temporaryFilename = sys_get_temp_dir()
        .DIRECTORY_SEPARATOR.'CloudObjects-CLI.phar';
      $targetFilename = '/usr/local/bin/cloudobjects'; // TODO: check system first

      // Update found, download it
      // TODO: find out how this works in Guzzle 6
      $output->writeln('Downloading new version ...');
      $file = fopen($temporaryFilename, 'w');
      $client->get($updateCheckResponse['source_url'])
        ->setResponseBody($file)
        ->send();

      // Install it
      $output->writeln("Installing in ".dirname($targetFilename)." ...");
      if (!rename($temporaryFilename, $targetFilename)) {
        $output->writeln("<error>Install failed!</error>");
        return 1;
      }

      // Make executable
      chmod($targetFilename, 0755);

      // Upate authorization
      $output->writeln('Authorizing ...');
      passthru('cloudobjects authorize '.$app['context']->getAAUID()
        .' '.$updateCheckResponse['code'], $returnCode);

      if ($returnCode==0) $output->writeln('Update complete!');

      return $returnCode;
    } else
      throw new \Exception("Unexpected server response!");
  }

}
