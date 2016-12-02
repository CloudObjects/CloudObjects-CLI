<?php

namespace CloudObjects\CLI\Commands;

use Symfony\Component\Console\Input\InputInterface, Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Cilex\Command\Command;
use CloudObjects\CLI\NotAuthorizedException, CloudObjects\CLI\UpdateChecker;
use CloudObjects\SDK\COIDParser;

class AttachmentGetCommand extends Command {

  protected function configure() {
    $this->setName('attachment:get')
      ->setDescription('Retrieve the contents of an attachment of an object.')
      ->addArgument('coid', InputArgument::REQUIRED, 'The COID of the object.')
      ->addArgument('filename', InputArgument::REQUIRED, 'The filename for the attachment.');
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $app = $this->getContainer();
    if (!isset($app['context'])) throw new NotAuthorizedException();

    $coid = COIDParser::fromString($input->getArgument('coid'));

    if (COIDParser::getType($coid)==COIDParser::COID_INVALID) {
      $output->writeln('<error>Invalid COID: '.(string)$coid.'</error>');
      return;
    }

    $filename = $input->getArgument('filename');

    $attachmentResponse = $app['context']->getClient()
      ->get('/ws/'.$coid->getHost().$coid->getPath().'/'.basename($filename));

    $output->writeln((string)$attachmentResponse->getBody());
    UpdateChecker::execute($app, $output);
  }

}
