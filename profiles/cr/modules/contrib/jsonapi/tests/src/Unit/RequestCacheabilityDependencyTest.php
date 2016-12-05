<?php

namespace Drupal\Tests\jsonapi\Unit;

use Drupal\jsonapi\RequestCacheabilityDependency;
use Drupal\Tests\UnitTestCase;

/**
 * Class RequestCacheabilityDependencyTest
 *
 * @package Drupal\Tests\jsonapi\Unit
 *
 * @coversDefaultClass \Drupal\jsonapi\RequestCacheabilityDependency
 *
 * @group jsonapi
 */
class RequestCacheabilityDependencyTest extends UnitTestCase {

  /**
   * Cacheable dependency under test.
   *
   * @var \Drupal\Core\Cache\CacheableDependencyInterface
   */
  protected $cacheableDependency;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->cacheableDependency = new RequestCacheabilityDependency();
  }


  /**
   * @covers ::getCacheContexts
   */
  public function testGetCacheContexts() {
    $this->assertArrayEquals([
      'url.query_args:filter',
      'url.query_args:sort',
      'url.query_args:page',
      'url.query_args:fields',
      'url.query_args:include',
    ], $this->cacheableDependency->getCacheContexts());
  }

  /**
   * @covers ::getCacheContexts
   */
  public function testGetCacheTags() {
    $this->assertArrayEquals([], $this->cacheableDependency->getCacheTags());
  }

  /**
   * @covers ::getCacheContexts
   */
  public function testGetCacheMaxAge() {
    $this->assertEquals(-1, $this->cacheableDependency->getCacheMaxAge());
  }

}
