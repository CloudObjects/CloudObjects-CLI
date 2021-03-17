<?php

/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/. */
 
namespace CloudObjects\CLI;

require_once __DIR__."/vendor/autoload.php";

date_default_timezone_set('UTC');

$app = new \Symfony\Component\Console\Application('CloudObjects CLI', '0.5');
CredentialManager::configure($app);
$app->add(new Commands\SelfUpdateCommand());
$app->add(new Commands\AuthorizeCommand());
$app->add(new Commands\DeauthorizeCommand());
$app->add(new Commands\AccountCommand());

$app->add(new Commands\DomainsListCommand());

$app->add(new Commands\ConfigurationJobCreateCommand());
$app->add(new Commands\ConfigurationJobMessagesCommand());

$app->add(new Commands\ObjectGetCommand());
$app->add(new Commands\ObjectEditForkCommand());
$app->add(new Commands\ObjectDeleteCommand());

// $app->add(new Commands\AccountGatewayGetAccessTokenCommand());

$app->add(new Commands\AttachmentGetCommand());
$app->add(new Commands\AttachmentPutCommand());
$app->add(new Commands\AttachmentDeleteCommand());

$app->add(new Commands\MembersListCommand);
$app->add(new Commands\MembersAddCommand);
$app->add(new Commands\MembersRemoveCommand);

$app->add(new Commands\ConsumersListCommand);
$app->add(new Commands\ConsumersAddCommand);
$app->add(new Commands\ConsumersRemoveCommand);

$app->add(new Commands\ProvidersListCommand);
$app->add(new Commands\ProvidersRemoveCommand);
$app->add(new Commands\ProvidersSecretGetCommand());

$app->run();
