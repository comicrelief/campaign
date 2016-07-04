<?php
/**
 * @file
 * Contains \Drupal\simple_sitemap\Plugin\simple_sitemap\LinkGenerator\Menu.
 *
 * Plugin for menu entity link generation.
 */

namespace Drupal\simple_sitemap\Plugin\simple_sitemap\LinkGenerator;

use Drupal\simple_sitemap\LinkGeneratorBase;

/**
 * Menu class.
 *
 * @LinkGenerator(
 *   id = "menu"
 * )
 */
class Menu extends LinkGeneratorBase {

  /**
   * {@inheritdoc}
   */
  function getQueryInfo() {
    return array(
      'field_info' => array(
        'entity_id' => 'mlid',
        'route_name' => 'route_name',
        'route_parameters' => 'route_parameters',
        'options' => 'options',
      ),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getQuery($bundle) {
    return $this->database->select('menu_tree', 'm')
      ->fields('m', array('mlid', 'route_name', 'route_parameters', 'options'))
      ->condition('menu_name', $bundle)
      ->condition('enabled', 1)
      ->condition('route_name', '', '!=');
  }
}
