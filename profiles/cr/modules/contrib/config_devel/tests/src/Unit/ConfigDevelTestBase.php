<?php

namespace Drupal\Tests\config_devel\Unit;

use org\bovigo\vfs\vfsStream;
use Drupal\Tests\UnitTestCase;

/**
 * Helper class with mock objects.
 */
abstract class ConfigDevelTestBase extends UnitTestCase {

  /**
   * @var \Drupal\Core\Config\ConfigFactoryInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $configFactory;

  /**
   * @var \Drupal\Core\Config\ConfigManagerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $configManager;

  /**
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $eventDispatcher;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->configFactory = $this->getMock('Drupal\Core\Config\ConfigFactoryInterface');

    $this->eventDispatcher = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');

    $this->configManager = $this->getMock('Drupal\Core\Config\ConfigManagerInterface');
    $this->configManager->expects($this->any())
      ->method('getEntityTypeIdByName')
      ->will($this->returnArgument(0));

    vfsStream::setup('public://');
  }
}
