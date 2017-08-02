<?php

namespace Drupal\cr_monolog\Service;

/**
 * Class LogglyService
 */
class LogglyService {

  public static function getToken() {
    return getenv('LOGGLY_TOKEN');
  }

  public static function getEnv() {
    return $_ENV['PLATFORM_BRANCH'];
  }
}
