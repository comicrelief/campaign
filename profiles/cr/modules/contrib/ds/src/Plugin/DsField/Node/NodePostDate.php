<?php

/**
 * @file
 * Contains \Drupal\ds\Plugin\DsField\Node\NodePostDate.
 */

namespace Drupal\ds\Plugin\DsField\Node;

use Drupal\ds\Plugin\DsField\Date;

/**
 * Plugin that renders the post date of a node.
 *
 * @DsField(
 *   id = "node_post_date",
 *   title = @Translation("Post date"),
 *   entity_type = "node",
 *   provider = "node"
 * )
 */
class NodePostDate extends Date {

  /**
   * {@inheritdoc}
   */
  public function getRenderKey() {
    return 'created';
  }

}
