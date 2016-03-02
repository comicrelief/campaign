<?php

/**
 * @file
 * Contains \Drupal\view_modes_display\Controller\DefaultController.
 */

namespace Drupal\view_modes_display\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\node\NodeInterface;
use Drupal\block_content\BlockContentInterface;
use Drupal\user\UserInterface;

/**
 * Class DefaultController.
 *
 * @package Drupal\view_modes_display\Controller
 */
class DefaultController extends ControllerBase {

  /**
   * @param \Drupal\node\NodeInterface $node
   * @return string
   */
  public function previewNode(NodeInterface $node) {
    return $this->preview($node);
  }

  /**
   * @param \Drupal\block_content\BlockContentInterface $block
   * @return string
   */
  public function previewBlockContent(BlockContentInterface $block_content) {
    return $this->preview($block_content);
  }

  /**
   * @param \Drupal\user\UserInterface $user
   * @return string
   */
  public function previewUser(UserInterface $user) {
    return $this->preview($user);
  }

  /**
   * Preview entity view modes.
   *
   * @return string
   */
  public function preview(ContentEntityInterface $entity) {
    $entity_manager = \Drupal::entityManager();
    $entity_type = $entity->getEntityType()->get('id');

    $view_modes_info = $entity_manager->getViewModes($entity_type);

    $config_prefix = 'core.entity_view_display';
    $entity_type_id = $entity->getEntityType()->id();

    $ids = \Drupal::configFactory()->listAll($config_prefix . '.' . $entity_type_id . '.' . $entity->bundle() . '.');

    $load_ids = array();
    foreach ($ids as $id) {
      $config_id = str_replace($config_prefix . '.', '', $id);
      list(,, $display_mode) = explode('.', $config_id);
      $load_ids[] = $config_id;
    }

    $enabled_display_modes = array();
    $displays = $entity_manager->getStorage('entity_view_display')->loadMultiple($load_ids);
    foreach ($displays as $display) {
      if ($display->status()) {
        $enabled_display_modes[] = $display->get('mode');
      }
    }

    // Loop through the view modes and render in-place
    $build = array();
    foreach ($view_modes_info as $view_mode_name => $view_mode_info) {
      if (in_array($view_mode_name, $enabled_display_modes)) {
        $markup = entity_view($entity, $view_mode_name);
        $build[] = [
          '#prefix' => '<div class="view-mode-list-item view-mode-list-item-' . $view_mode_name . '"><h1>' . $view_mode_info['label'] . '</h1>',
          '#markup' => render($markup),
          '#suffix' => '</div>',
        ];
      }
    }

    return $build;
  }

}
