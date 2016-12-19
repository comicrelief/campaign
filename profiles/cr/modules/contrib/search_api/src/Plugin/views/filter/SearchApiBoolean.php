<?php

namespace Drupal\search_api\Plugin\views\filter;

use Drupal\Core\Cache\UncacheableDependencyTrait;
use Drupal\views\Plugin\views\filter\BooleanOperator;

/**
 * Defines a filter for filtering on boolean values.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("search_api_boolean")
 */
class SearchApiBoolean extends BooleanOperator {

  use UncacheableDependencyTrait;
  use SearchApiFilterTrait;

}
