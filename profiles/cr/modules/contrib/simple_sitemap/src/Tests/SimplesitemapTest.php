<?php
/**
 * @file
 * Contains \Drupal\simple_sitemap\Tests\SimplesitemapTest
 */

namespace Drupal\simple_sitemap\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Tests Simple XML sitemap integration.
 *
 * @group simple_sitemap
 */
class SimplesitemapTest extends WebTestBase {

  protected $dumpHeaders = TRUE;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['simple_sitemap', 'node'];

  /**
   * Implements setup().
   */
  protected function setUp() {
    parent::setUp();

    $this->drupalCreateContentType(['type' => 'page']);
    $this->config('simple_sitemap.settings')
      ->set('entity_types', ['node' => ['page' =>  ['index' => 1, 'priority' => '0.5']]])
      ->save();
  }

  /**
   * Test Simple sitemap integration.
   */
  public function testSimplesitemap() {

    // Verify sitemap.xml has been generated on install (custom path generation).
    $this->drupalGet('sitemap.xml');
    $this->assertEqual($this->drupalGetHeader('X-Drupal-Cache'), 'MISS');
    $this->assertText('http://');
    $this->drupalGet('sitemap.xml');
    $this->assertEqual($this->drupalGetHeader('X-Drupal-Cache'), 'HIT');
    $this->assertText('http://');

    /* @var $node \Drupal\Node\NodeInterface */
    $node = $this->createNode(['title' => 'A new page', 'type' => 'page']);

    // Generate new sitemap.
    \Drupal::service('simple_sitemap.generator')->generateSitemap('nobatch');

    // Verify the cache was flushed and node is in the sitemap.
    $this->drupalGet('sitemap.xml');
    $this->assertEqual($this->drupalGetHeader('X-Drupal-Cache'), 'MISS');
    $this->assertText('node/' . $node->id());
    $this->drupalGet('sitemap.xml');
    $this->assertEqual($this->drupalGetHeader('X-Drupal-Cache'), 'HIT');
    $this->assertText('node/' . $node->id());
  }
}
