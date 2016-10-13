<?php

namespace Drupal\yamlform\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Adds the _admin_route option to form routes.
 */
class YamlFormRouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    foreach ($collection->all() as $route) {
      if (!$route->hasOption('_admin_route') && (
          strpos($route->getPath(), '/admin/structure/yamlform/') === 0
          || strpos($route->getPath(), '/yamlform/results/') !== FALSE
        )) {
        $route->setOption('_admin_route', TRUE);
      }
    }
  }

}
