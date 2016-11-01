<?php

namespace CloudObjects\CLI;

require_once __DIR__."/vendor/autoload.php";

date_default_timezone_set('UTC');

$app = new \Cilex\Application('CloudObjects CLI', '0.3');
CredentialManager::configure($app);
$app->command(new Commands\SelfUpdateCommand());
$app->command(new Commands\AuthorizeCommand());
$app->command(new Commands\DeauthorizeCommand());
$app->command(new Commands\AccountCommand());
$app->command(new Commands\ConfigurationJobCreateCommand());
$app->command(new Commands\ConfigurationJobMessagesCommand());
$app->command(new Commands\ObjectGetCommand());
$app->command(new Commands\SharedSecretGetCommand());
$app->command(new Commands\AccountGatewayGetAccessTokenCommand());
$app->command(new Commands\AttachmentGetCommand());
$app->command(new Commands\AttachmentPutCommand());

$app->command(new Commands\MembersListCommand);
$app->command(new Commands\MembersAddCommand);
$app->command(new Commands\MembersRemoveCommand);

$app->run();
