<?php

namespace Drupal\jsonapi;

use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\Core\Cache\UnchangingCacheableDependencyTrait;

/**
 * Class RequestCacheabilityDependency.
 */
class RequestCacheabilityDependency implements CacheableDependencyInterface {

  use UnchangingCacheableDependencyTrait;

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return array_map(function ($param_name) {
      return sprintf('url.query_args:%s', $param_name);
    }, $this::getQueryParamCacheContextList());
  }

  /**
   * Builds the list of URL query parameter names for the cache context.
   *
   * @return {string[]}
   *   The list of parameter names that vary the cache entry.
   */
  protected static function getQueryParamCacheContextList() {
    return ['filter', 'sort', 'page', 'fields', 'include'];
  }

}
