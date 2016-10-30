<?php

namespace CloudObjects\CLI\Commands;

use Symfony\Component\Console\Input\InputInterface, Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Cilex\Command\Command;
use CloudObjects\CLI\NotAuthorizedException, CloudObjects\CLI\UpdateChecker;
use CloudObjects\SDK\COIDParser;

class AttachmentPutCommand extends Command {

  protected function configure() {
    $this->setName('attachment:put')
      ->setAliases(array('attach'))
      ->setDescription('Upload the contents of a file as an attachment to an object.')
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
    if (!file_exists($filename)) throw new \Exception("Specified file not found.");

    $app['context']->getClient()
      ->put('/ws/'.$coid->getHost().$coid->getPath().'/'.basename($filename), [
          'body' => file_get_contents($filename)
        ]);

    $output->writeln('File uploaded successfully.');
    UpdateChecker::execute($app, $output);
  }

}
