<?php

namespace Drupal\ds\Plugin\DsField\Comment;

use Drupal\ds\Plugin\DsField\Date;

/**
 * Plugin that renders the changed date of a comment.
 *
 * @DsField(
 *   id = "comment_changed_date",
 *   title = @Translation("Last modified"),
 *   entity_type = "comment",
 *   provider = "comment"
 * )
 */
class CommentChangedDate extends Date {

  /**
   * {@inheritdoc}
   */
  public function getRenderKey() {
    return 'changed';
  }

}
