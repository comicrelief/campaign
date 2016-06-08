<?php

/**
 * @file
 * Contains \Drupal\purge\Tests\TagsHeader\ServiceTest.
 */

namespace Drupal\purge\Tests\TagsHeader;

use Drupal\purge\Tests\KernelTestBase;
use Drupal\purge\Tests\KernelServiceTestBase;
use Drupal\purge\Plugin\Purge\TagsHeader\TagsHeadersServiceInterface;
use Drupal\purge\Plugin\Purge\TagsHeader\TagsHeaderInterface;

/**
 * Tests \Drupal\purge\Plugin\Purge\TagsHeader\TagsHeadersService.
 *
 * @group purge
 * @see \Drupal\purge\Plugin\Purge\TagsHeader\TagsHeadersService
 * @see \Drupal\purge\Plugin\Purge\TagsHeader\TagsHeadersServiceInterface
 */
class ServiceTest extends KernelServiceTestBase {
  protected $serviceId = 'purge.tagsheaders';
  public static $modules = ['purge_tagsheader_test'];

  /**
   * All bundled plugins in purge core, including in the test module.
   *
   * @var string[]
   */
  protected $plugins = [
    'purge',
    'a',
    'b',
    'c',
  ];

  /**
   * Set up the test.
   */
  function setUp() {

    // Skip parent::setUp() as we don't want the service initialized here.
    KernelTestBase::setUp();
  }

  /**
   * Tests \Drupal\purge\Plugin\Purge\TagsHeader\TagsHeadersService::count
   */
  public function testCount() {
    $this->initializeService();
    $this->assertTrue($this->service instanceof \Countable);
    $this->assertEqual(4, count($this->service));
  }

  /**
   * Tests \Drupal\purge\Plugin\Purge\TagsHeader\TagsHeadersService::getPluginsEnabled
   */
  public function testGetPluginsEnabled() {
    $this->initializeService();
    $plugin_ids = $this->service->getPluginsEnabled();
    foreach ($this->plugins as $plugin_id) {
      $this->assertTrue(in_array($plugin_id, $plugin_ids));
    }
  }

  /**
   * Tests the \Iterator implementation.
   *
   * @see \Drupal\purge\Plugin\Purge\TagsHeader\TagsHeadersService::current
   * @see \Drupal\purge\Plugin\Purge\TagsHeader\TagsHeadersService::key
   * @see \Drupal\purge\Plugin\Purge\TagsHeader\TagsHeadersService::next
   * @see \Drupal\purge\Plugin\Purge\TagsHeader\TagsHeadersService::rewind
   * @see \Drupal\purge\Plugin\Purge\TagsHeader\TagsHeadersService::valid
   */
  public function testIteration() {
    $this->initializeService();
    $this->assertTrue($this->service instanceof \Iterator);
    $items = 0;
    foreach ($this->service as $instance) {
      $this->assertTrue($instance instanceof TagsHeaderInterface);
      $this->assertTrue(in_array($instance->getPluginId(), $this->plugins));
      $items++;
    }
    $this->assertEqual(4, $items);
    $this->assertFalse($this->service->current());
    $this->assertFalse($this->service->valid());
    $this->assertNull($this->service->rewind());
    $this->assertEqual('purge', $this->service->current()->getPluginId());
    $this->assertNull($this->service->next());
    $this->assertEqual('b', $this->service->current()->getPluginId());
    $this->assertTrue($this->service->valid());
  }

}
