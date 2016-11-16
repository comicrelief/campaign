<?php

/**
 * @file
 * Contains \Drupal\purge\Tests\TagsHeader\PurgeCacheTagsHeaderTest.
 */

namespace Drupal\purge\Tests\TagsHeader;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Drupal\purge\Tests\KernelTestBase;

/**
 * Tests \Drupal\purge\Plugin\Purge\TagsHeader\PurgeCacheTagsHeader.
 *
 * @group purge
 */
class PurgeCacheTagsHeaderTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = ['system'];

  /**
   * {@inheritdoc}
   */
  function setUp() {
    parent::setUp();
    $this->installSchema('system', ['router']);
    \Drupal::service('router.builder')->rebuild();
  }

  /**
   * Test that the header value is exactly as expected (space separated).
   */
  public function testHeaderValue() {
    $request = Request::create('/system/401');
    $response = $this->container->get('http_kernel')->handle($request);
    $this->assertEqual(200, $response->getStatusCode());
    $this->assertEqual($response->headers->get('Purge-Cache-Tags'), 'config:user.role.anonymous rendered');
  }

}
