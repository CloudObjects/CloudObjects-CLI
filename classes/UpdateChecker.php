<?php

/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/. */
 
namespace CloudObjects\CLI;

use Cilex\Application;
use Symfony\Component\Console\Output\OutputInterface;
use CloudObjects\SDK\COIDParser;

class UpdateChecker  {

  public static function execute(Application $app, OutputInterface $output) {
    if ($app['context']->isNewAccessorVersionAvailable()) {
      $output->getErrorOutput()->writeln('<comment>You are using '.$app['console.name']
        . ' '.$app['console.version'].'. Version '
        . COIDParser::getVersion($app['context']->getLatestAccessorVersionCOID())
        . ' is available. Updating is recommended.</comment>');
    }
  }

}
