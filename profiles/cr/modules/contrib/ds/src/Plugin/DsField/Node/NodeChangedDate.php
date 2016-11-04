<?php

namespace Drupal\ds\Plugin\DsField\Node;

use Drupal\ds\Plugin\DsField\Date;

/**
 * Plugin that renders the post date of a node.
 *
 * @DsField(
 *   id = "node_changed_date",
 *   title = @Translation("Last modified"),
 *   entity_type = "node",
 *   provider = "node"
 * )
 */
class NodeChangedDate extends Date {

  /**
   * {@inheritdoc}
   */
  public function getRenderKey() {
    return 'changed';
  }

}
