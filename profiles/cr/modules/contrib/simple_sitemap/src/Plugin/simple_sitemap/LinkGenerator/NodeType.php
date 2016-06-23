<?php
/**
 * @file
 * Contains \Drupal\simple_sitemap\Plugin\simple_sitemap\LinkGenerator\NodeType.
 *
 * Plugin for node entity link generation.
 */

namespace Drupal\simple_sitemap\Plugin\simple_sitemap\LinkGenerator;

use Drupal\simple_sitemap\LinkGeneratorBase;

/**
 * NodeType class.
 *
 * @LinkGenerator(
 *   id = "node_type",
 *   entity_type_name = "node"
 * )
 */
class NodeType extends LinkGeneratorBase {

  /**
   * {@inheritdoc}
   */
  public function getQueryInfo() {
    return array(
      'field_info' => array(
        'entity_id' => 'nid',
        'lastmod' => 'changed',
      ),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getQuery($bundle) {
    return $this->database->select('node_field_data', 'n')
      ->fields('n', array('nid', 'changed'))
      ->condition('type', $bundle)
      ->condition('status', 1);
  }
}
