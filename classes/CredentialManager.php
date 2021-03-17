<?php

/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/. */
 
namespace CloudObjects\CLI;

use ML\IRI\IRI;
use Symfony\Component\Console\Application;
use CloudObjects\SDK\AccountGateway\AAUIDParser,
    CloudObjects\SDK\AccountGateway\AccountContext;

class CredentialManager  {

    private static $context;

    public static function getContext() {
        return self::$context;
    }

    private static function getFilename() {
        return getenv('HOME').DIRECTORY_SEPARATOR.'.cloudobjects';
    }

    public static function configure(Application $app) {
        if (getenv('CO_AAUID') !== false && getenv('CO_ACCESS_TOKEN') !== false) {
            // If environmental variables are set, take them as first priority
            self::$context = new AccountContext(
                AAUIDParser::fromString(getenv('CO_AAUID')),
                getenv('CO_ACCESS_TOKEN')
            );
        } else
        if (file_exists(self::getFilename())) {
            // Otherwise, load stored authorization
            $data = json_decode(file_get_contents(self::getFilename()), true);
            if (isset($data['aauid']) && isset($data['access_token'])
                    && isset($data['version']) && $data['version'] == $app->getVersion()) {
                self::$context = new AccountContext(
                    AAUIDParser::fromString($data['aauid']),
                    $data['access_token']
                );
            }
        }
    }

    public static function setAuthorizedWithCredentials(Application $app, $aauid, $accessToken) {
        file_put_contents(self::getFileName(), json_encode([
            'aauid' => $aauid,
            'access_token' => $accessToken,
            'version' => $app->getVersion()
        ]));
    }

    public static function setUnauthorized() {
        if (file_exists(self::getFilename())) {
            unlink(self::getFilename());
        }
    }

}
