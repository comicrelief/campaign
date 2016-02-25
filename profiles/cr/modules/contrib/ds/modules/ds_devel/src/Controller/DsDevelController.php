<?php

/**
 * @file
 * Contains \Drupal\ds_devel\Controller\DsDevelController.
 */

namespace Drupal\ds_devel\Controller;

use Drupal\Component\Utility\Html;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Url;

/**
 * Returns responses for Views UI routes.
 */
class DsDevelController {

  /**
   * Lists all instances of fields on any views.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *    A RouteMatch object.
   *
   * @return array
   *   The Views fields report page.
   */
  public function entityMarkup(RouteMatchInterface $route_match) {

    $parameter_name = $route_match->getRouteObject()->getOption('_devel_entity_type_id');
    $entity = $route_match->getParameter($parameter_name);
    $entity_type_id = $entity->getEntityTypeId();

    $key = \Drupal::request()->get('key', 'default');

    $builded_entity = entity_view($entity, $key);
    $markup = \Drupal::service('renderer')->render($builded_entity);

    $links = array();
    $active_view_modes = \Drupal::service('entity_display.repository')->getViewModeOptionsByBundle($entity_type_id, $entity->bundle());
    foreach ($active_view_modes as $id => $label) {
      $links[] = array(
        'title' => $label,
        'url' => Url::fromRoute("entity.$entity_type_id.devel_markup", array($entity_type_id => $entity->id(), 'key' => $id)),
      );
    }

    $build['links'] = array(
      '#theme' => 'links',
      '#links' => $links,
      '#prefix' => '<hr/><div>',
      '#suffix' => '</div><hr />',
    );
    $build['markup'] = [
      '#markup' => '<code><pre>' . Html::escape($markup) . '</pre></code>',
      '#cache' => array(
        'max-age' => 0,
      ),
      '#allowed_tags' => ['code', 'pre'],
    ];

    return $build;
  }

}
