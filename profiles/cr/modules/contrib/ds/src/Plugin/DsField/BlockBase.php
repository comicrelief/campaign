<?php

/**
 * @file
 * Contains \Drupal\ds\Plugin\DsField\BlockBase.
 */

namespace Drupal\ds\Plugin\DsField;

use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Render\Element;

/**
 * The base plugin to create DS block fields.
 */
abstract class BlockBase extends DsFieldBase {

  /**
   * The block.
   *
   * @var \Drupal\Core\Block\BlockPluginInterface
   */
  protected $block;

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Get block
    $block = $this->getBlock();

    // Apply block config.
    $block_config = $this->blockConfig();
    $block->setConfiguration($block_config);

    // Get render array.
    $block_elements = $block->build();

    // Return an empty array if there is nothing to render.
    return Element::isEmpty($block_elements) ? [] : $block_elements;
  }

  /**
   * Returns the plugin ID of the block.
   */
  protected function blockPluginId() {
    return '';
  }

  /**
   * Returns the config of the block.
   */
  protected function blockConfig() {
    return array();
  }

  /**
   * Return the block entity.
   */
  protected function getBlock() {
    if (!$this->block) {
      $manager = \Drupal::service('plugin.manager.block');

      // Create an instance of the block.
      /** @var $block BlockPluginInterface */
      $block_id = $this->blockPluginId();
      $block = $manager->createInstance($block_id);

      $this->block = $block;
    }

    return $this->block;
  }
}
