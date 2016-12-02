<?php

namespace CloudObjects\CLI\Commands;

use Symfony\Component\Console\Input\InputInterface, Symfony\Component\Console\Input\InputArgument,
Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Cilex\Command\Command;
use CloudObjects\CLI\NotAuthorizedException, CloudObjects\CLI\UpdateChecker;
use CloudObjects\SDK\COIDParser;

class ObjectGetCommand extends Command {

  protected function configure() {
    $this->setName('object:get')
      ->setAliases(array('get'))
      ->setDescription('Get the description of an object.')
      ->addArgument('coid', InputArgument::REQUIRED, 'The COID of the object.')
      ->addOption('format', null, InputOption::VALUE_OPTIONAL, 'Format for the object description.', 'xml');
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $app = $this->getContainer();
    if (!isset($app['context'])) throw new NotAuthorizedException();

    $coid = COIDParser::fromString($input->getArgument('coid'));

    if (COIDParser::getType($coid)==COIDParser::COID_INVALID) {
      $output->writeln('<error>Invalid COID: '.(string)$coid.'</error>');
      return;
    }

    switch ($input->getOption('format')) {
      case "xml":
        $mimeType = 'application/rdf+xml';
        break;
      case "turtle":
        $mimeType = 'text/turtle';
        break;
      case "triples":
        $mimeType = 'text/plain';
        break;
      case "jsonraw":
        $mimeType = 'application/json';
        break;
      case "jsonld":
        $mimeType = 'application/ld+json';
        break;
      default:
        throw new \Exception('Unsupported format!');
    }

    $objectResponse = $app['context']->getClient()
      ->get('/ws/'.$coid->getHost().$coid->getPath().'/object', [
        'headers' => ['Accept' => $mimeType]
      ]);

    $output->writeln((string)$objectResponse->getBody());
    UpdateChecker::execute($app, $output);
  }

}
