<?php

namespace Drupal\cr_monolog\Processor;

/**
 * Class EnvironmentProcessor
 */
class EnvironmentProcessor {

  public function __invoke(array $record) {
    $record['context']['env'] = $_ENV['PLATFORM_BRANCH'];
    return $record;
  }
}
