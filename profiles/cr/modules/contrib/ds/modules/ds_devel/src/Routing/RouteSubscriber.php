<?php

/**
 * @file
 * Contains \Drupal\ds_devel\Routing\RouteSubscriber.
 */

namespace Drupal\ds_devel\Routing;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\RouteSubscriberBase;
use Drupal\Core\Routing\RoutingEvents;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Subscriber for Devel routes.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * The entity manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new RouteSubscriber object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    foreach ($this->entityTypeManager->getDefinitions() as $entity_type_id => $entity_type) {
      if ($entity_type->hasLinkTemplate('devel-markup')) {
        $options = array(
          '_admin_route' => TRUE,
          '_devel_entity_type_id' => $entity_type_id,
          'parameters' => array(
            $entity_type_id => array(
              'type' => 'entity:' . $entity_type_id,
            ),
          ),
        );

        if ($devel_render = $entity_type->getLinkTemplate('devel-markup')) {
          $route = new Route(
            $devel_render,
            array(
              '_controller' => '\Drupal\ds_devel\Controller\DsDevelController::entityMarkup',
              '_title' => 'Devel Render',
            ),
            array('_permission' => 'access devel information'),
            $options
          );

          $collection->add("entity.$entity_type_id.devel_markup", $route);
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = parent::getSubscribedEvents();
    $events[RoutingEvents::ALTER] = array('onAlterRoutes', 100);
    return $events;
  }

}
