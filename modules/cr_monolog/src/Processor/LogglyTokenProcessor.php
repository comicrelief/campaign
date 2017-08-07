<?php

namespace Drupal\cr_monolog\Processor;

/**
 * Class LogglyTokenProcessor
 */
class LogglyTokenProcessor {

  public function get() {
    return getenv('LOGGLY_TOKEN');
  }
}
