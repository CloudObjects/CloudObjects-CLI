<?php

namespace CloudObjects\CLI\Commands;

use Symfony\Component\Console\Input\InputInterface, Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Cilex\Command\Command;
use CloudObjects\CLI\NotAuthorizedException, CloudObjects\CLI\UpdateChecker;

class ConfigurationJobMessagesCommand extends Command {

  protected function configure() {
    $this->setName('configuration-job:messages')
      ->setAliases(array('messages', 'log'))
      ->setDescription('Fetch and read all messages from the configuration job log.');
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $app = $this->getContainer();
    if (!isset($app['context'])) throw new NotAuthorizedException();

    $logResponse = json_decode($app['context']->getClient()->delete('/ws/messages', [
      'headers' => [ 'Content-Length' => 0 ]])->getBody(), true);

    if (isset($logResponse['messages']) && count($logResponse['messages'])==0) {
      $output->writeln('There are no messages available.');
    } else
    if (isset($logResponse['messages'])) {
      foreach ($logResponse['messages'] as $m) {
        switch ($m['type']) {
          case "SUCCESS":
            $output->writeln('<info>SUCCESS</info>: '.$m['content']);
            break;
          case "FAILURE":
            $output->writeln('<error>FAILURE</error>: '.$m['content']);
            break;
          default:
            $output->writeln($m['type'].': '.$m['content']);
        }
        $output->writeln('['.$m['created_at'].']');
        $output->writeln('');
      }
    } else {
      throw new \Exception("Unexpected response from server.");
    }
    UpdateChecker::execute($app, $output);
  }

}
