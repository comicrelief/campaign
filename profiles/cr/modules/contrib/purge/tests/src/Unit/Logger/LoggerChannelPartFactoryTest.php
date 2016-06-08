<?php

/**
 * @file
 * Contains \Drupal\Tests\purge\Unit\Logger\LoggerChannelPartFactoryTest.
 */

namespace Drupal\Tests\purge\Unit\Logger;

use Drupal\purge\Logger\LoggerChannelPartFactory;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\purge\Logger\LoggerChannelPartFactory
 * @group purge
 */
class LoggerChannelPartFactoryTest extends UnitTestCase {

  /**
   * The tested factory.
   *
   * @var \Drupal\purge\Logger\LoggerChannelPartFactory
   */
  protected $loggerChannelPartFactory;

  /**
   * The mocked logger channel.
   *
   * @var \PHPUnit_Framework_MockObject_MockObject|\Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $loggerChannelPurge;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    $this->loggerChannelPurge = $this->getMock('\Drupal\Core\Logger\LoggerChannelInterface');
    $this->loggerChannelPartFactory = new LoggerChannelPartFactory($this->loggerChannelPurge);
  }

  /**
   * @covers ::create
   *
   * @dataProvider providerTestCreate()
   */
  public function testCreate($id, array $grants = []) {
    $this->assertInstanceOf(
      '\Drupal\purge\Logger\LoggerChannelPart',
      $this->loggerChannelPartFactory->create($id, $grants)
    );
  }

  /**
   * Provides test data for testCreate().
   */
  public function providerTestCreate() {
    return [
      ['foo', [0,1,2]],
      ['bar', [1,2,3]],
      ['baz', [2,3,4]]
    ];
  }

}
