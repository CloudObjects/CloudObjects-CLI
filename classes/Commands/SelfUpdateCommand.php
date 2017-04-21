<?php

/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/. */
 
namespace CloudObjects\CLI\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Cilex\Command\Command;
use CloudObjects\CLI\NotAuthorizedException;
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

    $aauid = AAUIDParser::getAAUID($app['context']->getAAUID());
    $client = new Client([
      'base_uri' => 'https://'.$aauid.'.aauid.net/'
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
      $client->get($updateCheckResponse['source_url'], [
        'sink' => $temporaryFilename
      ]);

      // Install it
      $output->writeln("Installing in ".dirname($targetFilename)." ...");
      if (!rename($temporaryFilename, $targetFilename)) {
        $output->writeln("<error>Install failed!</error>");
        return 1;
      }

      // Make executable
      chmod($targetFilename, 0755);

      // Update authorization
      $output->writeln('Authorizing ...');
      passthru('cloudobjects authorize '.$aauid.' '.$updateCheckResponse['code'], $returnCode);

      if ($returnCode==0) $output->writeln('Update complete!');

      return $returnCode;
    } else
      throw new \Exception("Unexpected server response!");
  }

}
