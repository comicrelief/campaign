<?php

namespace Drupal\ds\Plugin\DsField\Comment;

use Drupal\ds\Plugin\DsField\Field;

/**
 * Plugin that renders the author of a comment.
 *
 * @DsField(
 *   id = "comment_author",
 *   title = @Translation("Author"),
 *   entity_type = "comment",
 *   provider = "comment"
 * )
 */
class CommentAuthor extends Field {

  /**
   * {@inheritdoc}
   */
  protected function entityRenderKey() {
    return 'name';
  }

}
