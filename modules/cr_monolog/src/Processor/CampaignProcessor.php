<?php

namespace Drupal\cr_monolog\Processor;

/**
 * Class EnvironmentProcessor
 */
class CampaignProcessor {

  public function __invoke(array $record) {
    $record['context']['campaign'] = getenv('CAMPAIGN');
    return $record;
  }
}
