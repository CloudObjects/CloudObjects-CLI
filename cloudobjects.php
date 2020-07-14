<?php

/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/. */
 
namespace CloudObjects\CLI;

require_once __DIR__."/vendor/autoload.php";

date_default_timezone_set('UTC');

$app = new \Cilex\Application('CloudObjects CLI', '0.3');
CredentialManager::configure($app);
$app->command(new Commands\SelfUpdateCommand());
$app->command(new Commands\AuthorizeCommand());
$app->command(new Commands\DeauthorizeCommand());
$app->command(new Commands\AccountCommand());

$app->command(new Commands\DomainsListCommand());

$app->command(new Commands\ConfigurationJobCreateCommand());
$app->command(new Commands\ConfigurationJobMessagesCommand());

$app->command(new Commands\ObjectGetCommand());
$app->command(new Commands\ObjectEditForkCommand());
$app->command(new Commands\ObjectDeleteCommand());

// $app->command(new Commands\AccountGatewayGetAccessTokenCommand());

$app->command(new Commands\AttachmentGetCommand());
$app->command(new Commands\AttachmentPutCommand());
$app->command(new Commands\AttachmentDeleteCommand());

$app->command(new Commands\MembersListCommand);
$app->command(new Commands\MembersAddCommand);
$app->command(new Commands\MembersRemoveCommand);

$app->command(new Commands\ConsumersListCommand);
$app->command(new Commands\ConsumersAddCommand);
$app->command(new Commands\ConsumersRemoveCommand);

$app->command(new Commands\ProvidersListCommand);
$app->command(new Commands\ProvidersRemoveCommand);
$app->command(new Commands\ProvidersSecretGetCommand());

$app->run();
