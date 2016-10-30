<?php

namespace CloudObjects\CLI\Commands;

use Symfony\Component\Console\Input\InputInterface, Symfony\Component\Console\Input\InputArgument,
Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Cilex\Command\Command;
use CloudObjects\CLI\NotAuthorizedException, CloudObjects\CLI\UpdateChecker;
use CloudObjects\SDK\COIDParser;

class SharedSecretGetCommand extends Command {

  protected function configure() {
    $this->setName('shared-secret:get')
      ->setAliases(array('secret'))
      ->setDescription('Get the shared secret between two namespaces.')
      ->addArgument('coid1', InputArgument::REQUIRED, 'The COID of the first namespace or any object within.')
      ->addArgument('coid2', InputArgument::REQUIRED, 'The COID of the second namespace or any object within.');
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $app = $this->getContainer();
    if (!isset($app['context'])) throw new NotAuthorizedException();

    $coid1 = COIDParser::fromString($input->getArgument('coid1'));
    $coid2 = COIDParser::fromString($input->getArgument('coid2'));

    if (COIDParser::getType($coid1)==COIDParser::COID_INVALID) {
      $output->writeln('<error>Invalid COID: '.(string)$coid1.'</error>');
      return;
    }
    if (COIDParser::getType($coid2)==COIDParser::COID_INVALID) {
      $output->writeln('<error>Invalid COID: '.(string)$coid2.'</error>');
      return;
    }

    $secretResponse = $app['context']->getClient()
      ->get('/ws/'.$coid1->getHost().'/sharedSecret:'.$coid2->getHost())
      ->send();

    $output->writeln($secretResponse->getBody(true));
    UpdateChecker::execute($app, $output);
  }

}
