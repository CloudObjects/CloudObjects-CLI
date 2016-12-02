<?php

namespace CloudObjects\CLI;

use Cilex\Application;
use ML\IRI\IRI;
use CloudObjects\SDK\AccountGateway\AccountContext;

class CredentialManager  {

  private static function getFilename() {
    return getenv('HOME').DIRECTORY_SEPARATOR.'.cloudobjects';
  }

  public static function configure(Application $app) {
    if (file_exists(self::getFilename())) {
      $data = json_decode(file_get_contents(self::getFilename()), true);
      if (isset($data['aauid']) && isset($data['access_token'])
          && isset($data['version']) && $data['version'] == $app['console.version']) {
        $app['context'] = new AccountContext(new IRI('aauid:'.$data['aauid']), $data['access_token']);
      }
    }
  }

  public static function setAuthorizedWithCredentials(Application $app, $aauid, $accessToken) {
    file_put_contents(self::getFileName(), json_encode(array(
      'aauid' => $aauid,
      'access_token' => $accessToken,
      'version' => $app['console.version']
    )));
  }

  public static function setUnauthorized() {
    if (file_exists(self::getFilename())) {
      unlink(self::getFilename());
    }
  }

}
