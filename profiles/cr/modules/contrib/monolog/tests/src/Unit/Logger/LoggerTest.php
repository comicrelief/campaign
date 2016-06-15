<?php

/**
 * @file
 * Contains \Drupal\Tests\monolog\Unit\Logger\LoggerTest.
 */

namespace Drupal\Tests\monolog\Unit\Logger;

use Drupal\Core\Logger\RfcLogLevel;
use Drupal\Tests\UnitTestCase;
use Drupal\monolog\Logger\Logger;
use Drupal\monolog\Logger\MonologLogLevel;

/**
 * @coversDefaultClass \Drupal\monolog\Logger\Logger
 * @group monolog
 */
class LoggerTest extends UnitTestCase {

  /**
   * Make sure that the level gets translated before sent to processors.
   * @covers ::addRecord
   * @dataProvider providerTestAddRecord
   */
  public function testAddRecord($log_level, $handler_log_level, $log_message) {
    $mock = $this->getMock('Monolog\Handler\NullHandler');
    $mock->expects($this->once())
      ->method('isHandling')
      ->will($this->returnValue(TRUE));

    $mock->expects($this->once())
      ->method('handle')
      ->with($this->callback(function(array $record) use ($handler_log_level, $log_message) {
        return $record['level'] === $handler_log_level && $record['message'] === $log_message;
      }));

    $logger = new Logger('Foo channel', [$mock]);
    $logger->addRecord($log_level, $log_message);
  }

  /**
   * Data provider for self::testAddRecord().
   */
  public function providerTestAddRecord() {
    return [
      [RfcLogLevel::DEBUG, MonologLogLevel::DEBUG, 'apple'],
      [RfcLogLevel::CRITICAL, MonologLogLevel::CRITICAL, 'banana'],
      [RfcLogLevel::CRITICAL, MonologLogLevel::CRITICAL, 'orange'],
      [RfcLogLevel::INFO, MonologLogLevel::INFO, 'cucumber'],
    ];
  }

}
