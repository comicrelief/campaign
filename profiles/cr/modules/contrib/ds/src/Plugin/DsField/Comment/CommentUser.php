<?php

/**
 * @file
 * Contains \Drupal\ds\Plugin\DsField\Comment\CommentUser.
 */

namespace Drupal\ds\Plugin\DsField\Comment;

use Drupal\ds\Plugin\DsField\Entity;
use Drupal\node\NodeInterface;

/**
 * Plugin that renders a view mode.
 *
 * @DsField(
 *   id = "comment_user",
 *   title = @Translation("User"),
 *   entity_type = "comment",
 *   provider = "user"
 * )
 */
class CommentUser extends Entity {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $view_mode = $this->getEntityViewMode();

    /** @var $comment CommentInterface */
    $comment = $this->entity();
    $uid = $comment->getOwnerId();

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
