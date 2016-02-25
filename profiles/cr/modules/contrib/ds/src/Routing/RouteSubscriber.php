<?php

/**
 * @file
 * Contains \Drupal\ds\Routing\RouteSubscriber.
 */

namespace Drupal\ds\Routing;

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
      $base_table = $entity_type->getBaseTable();
      if ($entity_type->get('field_ui_base_route') && !empty($base_table)) {

        if ($display = $entity_type->getLinkTemplate('display')) {
          $route = new Route(
            $display,
            array(
              '_controller' => '\Drupal\ds\Controller\DsController::contextualTab',
              '_title' => 'Manage display',
              'entity_type_id' => $entity_type_id,
            ),
            array(
              '_field_ui_view_mode_access' => 'administer ' . $entity_type_id . ' display'
            ),
            array(
              '_admin_route' => TRUE,
              '_ds_entity_type_id' => $entity_type_id,
              'parameters' => array(
                $entity_type_id => array(
                  'type' => 'entity:' . $entity_type_id,
                ),
              ),
            )
          );

          $collection->add("entity.$entity_type_id.display", $route);
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
