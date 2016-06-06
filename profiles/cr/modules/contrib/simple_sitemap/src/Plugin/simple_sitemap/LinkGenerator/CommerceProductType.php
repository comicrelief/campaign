<?php
/**
 * @file
 * Contains \Drupal\simple_sitemap\Plugin\simple_sitemap\LinkGenerator\CommerceProductType.
 *
 * Plugin for commerce product entity link generation.
 */

namespace Drupal\simple_sitemap\Plugin\simple_sitemap\LinkGenerator;

use Drupal\simple_sitemap\LinkGeneratorBase;

/**
 * CommerceProductType class.
 *
 * @LinkGenerator(
 *   id = "commerce_product_type",
 *   entity_type_name = "commerce_product"
 * )
 */
class CommerceProductType extends LinkGeneratorBase {

  /**
   * {@inheritdoc}
   */
  public function getQueryInfo() {
    return array(
      'field_info' => array(
        'entity_id' => 'product_id',
        'lastmod' => 'changed',
      ),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getQuery($bundle) {
    return $this->database->select('commerce_product_field_data', 'p')
      ->fields('p', array('product_id', 'changed'))
      ->condition('type', $bundle)
      ->condition('status', 1);
  }
}
