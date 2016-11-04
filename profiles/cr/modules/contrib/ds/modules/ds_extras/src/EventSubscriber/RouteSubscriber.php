<?php

namespace Drupal\ds_extras\EventSubscriber;

use Drupal\Core\Routing\RouteBuildEvent;
use Drupal\Core\Routing\RoutingEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Alter the node view route.
 */
class RouteSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[RoutingEvents::ALTER][] = array('alterRoutes', 100);
    return $events;
  }

  /**
   * Alters the routes.
   *
   * @param \Drupal\Core\Routing\RouteBuildEvent $event
   *   The event to process.
   */
  public function alterRoutes(RouteBuildEvent $event) {
    if (\Drupal::config('ds_extras.settings')->get('override_node_revision')) {
      $route = $event->getRouteCollection()->get('entity.node.revision');
      if (!empty($route)) {
        $route->setDefault('_controller', '\Drupal\ds_extras\Controller\DsExtrasController::revisionShow');
      }
    }
  }

}
