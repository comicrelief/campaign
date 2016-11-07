<?php

namespace Drupal\ds\Plugin\DsField\Node;

use Drupal\ds\Plugin\DsField\Title;

/**
 * Plugin that renders the title of a node.
 *
 * @DsField(
 *   id = "node_title",
 *   title = @Translation("Title"),
 *   entity_type = "node",
 *   provider = "node"
 * )
 */
class NodeTitle extends Title {

}
