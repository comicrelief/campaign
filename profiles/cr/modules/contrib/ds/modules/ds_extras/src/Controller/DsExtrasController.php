<?php

namespace Drupal\ds_extras\Controller;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;

/**
 * Returns responses for Display Suite Extra routes.
 */
class DsExtrasController extends ControllerBase {

  /**
   * Returns an node through JSON.
   *
   * @param Request $request
   *   The global request object.
   * @param string $entityType
   *   The type of the requested entity.
   * @param string $entityId
   *   The id of the requested entity.
   * @param string $viewMode
   *   The view mode you wish to render for the requested entity.
   *
   * @return array
   *   The Views fields report page.
   */
  public function switchViewMode(Request $request, $entityType, $entityId, $viewMode) {
    $response = new AjaxResponse();
    $entity = entity_load($entityType, $entityId);

    if ($entity->access('view')) {
      $element = entity_view($entity, $viewMode);
      $content = \Drupal::service('renderer')->render($element, FALSE);

      $response->addCommand(new ReplaceCommand('.' . $request->get('selector'), $content));
    }

    return $response;
  }

  /**
   * Displays a node revision.
   *
   * @param int $node_revision
   *   The node revision ID.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function revisionShow($node_revision) {
    /* @var \Drupal\node\NodeInterface $node */
    $node = $this->entityTypeManager()
      ->getStorage('node')
      ->loadRevision($node_revision);

    // Determine view mode.
    $view_mode = \Drupal::config('ds_extras.settings')
      ->get('override_node_revision_view_mode');

    drupal_static('ds_view_mode', $view_mode);

    $page = node_view($node, $view_mode);
    unset($page['nodes'][$node->id()]['#cache']);

    return $page;
  }

  /**
   * Checks access for the switch view mode route.
   */
  public function accessSwitchViewMode() {
    return $this->config('ds_extras.settings')
      ->get('switch_field') ? AccessResult::allowed() : AccessResult::forbidden();
  }

}
