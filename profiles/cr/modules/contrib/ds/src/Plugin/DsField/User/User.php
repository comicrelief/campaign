<?php

/**
 * @file
 * Contains \Drupal\ds\Plugin\DsField\User\User.
 */

namespace Drupal\ds\Plugin\DsField\User;

use Drupal\ds\Plugin\DsField\Entity;
use Drupal\node\NodeInterface;

/**
 * Plugin that renders a view mode.
 *
 * @DsField(
 *   id = "user",
 *   title = @Translation("User"),
 *   entity_type = "node",
 *   provider = "user"
 * )
 */
class User extends Entity {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $view_mode = $this->getEntityViewMode();

    /** @var $node NodeInterface */
    $node = $this->entity();
    $uid = $node->getOwnerId();

    $user = entity_load('user', $uid);
    $build = entity_view($user, $view_mode);

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function linkedEntity() {
    return 'user';
  }

}
