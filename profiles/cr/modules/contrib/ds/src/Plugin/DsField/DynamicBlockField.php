<?php

/**
 * @file
 * Contains \Drupal\ds\Plugin\DsField\DynamicBlockField.
 */

namespace Drupal\ds\Plugin\DsField;

use Drupal\Core\Block\BlockPluginInterface;
use Drupal\views\Plugin\Block\ViewsBlock;

/**
 * Defines a generic dynamic block field.
 *
 * @DsField(
 *   id = "dynamic_block_field",
 *   deriver = "Drupal\ds\Plugin\Derivative\DynamicBlockField",
 *   provider = "block"
 * )
 */
class DynamicBlockField extends BlockBase {

  /**
   * {@inheritdoc}
   */
  protected function blockPluginId() {
    $definition = $this->getPluginDefinition();
    return $definition['properties']['block'];
  }

  /**
   * {@inheritdoc}
   */
  protected function blockConfig() {
    $block_config = array();
    $definition = $this->getPluginDefinition();
    if (isset($definition['properties']['config'])) {
      $block_config = $definition['properties']['config'];
    }

    return $block_config;
  }

  /**
   * Returns the title of the block.
   */
  public function getTitle() {
    $field = $this->getFieldConfiguration();
    $title = $field['title'];

    if (isset($field['properties']['use_block_title']) && $field['properties']['use_block_title'] == TRUE) {
      /** @var $block BlockPluginInterface */
      $block = $this->getBlock();

      if ($block instanceof ViewsBlock) {
        $title = $block->build()['#title'];
      } else {
        $title = $block->label();
      }
    }

    return $title;
  }

}
