<?php

namespace Drupal\ds\Plugin\DsField\Comment;

use Drupal\ds\Plugin\DsField\Date;

/**
 * Plugin that renders the post date of a comment.
 *
 * @DsField(
 *   id = "comment_post_date",
 *   title = @Translation("Post date"),
 *   entity_type = "comment",
 *   provider = "comment"
 * )
 */
class CommentPostDate extends Date {

  /**
   * Returns the entity render key for this field.
   */
  public function getRenderKey() {
    return 'created';
  }

}
