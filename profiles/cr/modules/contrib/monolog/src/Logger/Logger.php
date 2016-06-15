<?php

/**
 * @file
 * Contains \Drupal\monolog\Logger\Logger.
 */

namespace Drupal\monolog\Logger;

use Drupal\Core\Logger\RfcLogLevel;
use Monolog\Logger as BaseLogger;

/**
 * Logger class for the Drupal Monolog module.
 *
 * Allows the channel to be modified after the class is instantiated. This is
 * normally not a good idea, but it is necessary to reconcile the differences in
 * the Monolog library and how the watchdog type relates to the logging
 * facility.
 */
class Logger extends BaseLogger {

  /**
   * Map of RFC 5424 log constants to Monolog log constants.
   *
   * @var array
   */
  protected $levelTranslation = array(
    RfcLogLevel::EMERGENCY => MonologLogLevel::EMERGENCY,
    RfcLogLevel::ALERT => MonologLogLevel::ALERT,
    RfcLogLevel::CRITICAL => MonologLogLevel::CRITICAL,
    RfcLogLevel::ERROR => MonologLogLevel::ERROR,
    RfcLogLevel::WARNING => MonologLogLevel::WARNING,
    RfcLogLevel::NOTICE => MonologLogLevel::NOTICE,
    RfcLogLevel::INFO => MonologLogLevel::INFO,
    RfcLogLevel::DEBUG => MonologLogLevel::DEBUG,
  );

  public function addRecord($level, $message, array $context = array()) {
    if (array_key_exists($level, $this->levelTranslation)) {
      $level = $this->levelTranslation[$level];
    }

    parent::addRecord($level, $message, $context);
  }
}
