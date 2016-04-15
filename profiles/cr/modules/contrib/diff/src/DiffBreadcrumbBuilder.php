<?php

/**
 * @file
 * Contains \Drupal\diff\DiffBreadcrumbBuilder.
 */

namespace Drupal\diff;

use Drupal\system\PathBasedBreadcrumbBuilder;
use Drupal\Core\Routing\RouteMatchInterface;

/**
 * Class to define the diff breadcrumb builder.
 */
class DiffBreadcrumbBuilder extends PathBasedBreadcrumbBuilder {

  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $route_match) {
    if ($route_match->getRouteName() == 'diff.revisions_diff') {
      if ($route_match->getParameter('filter') == 'raw-plain') {
        return TRUE;
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function build(RouteMatchInterface $route_match) {
    $build = parent::build($route_match);
    array_pop($build);

    return $build;
  }
}
