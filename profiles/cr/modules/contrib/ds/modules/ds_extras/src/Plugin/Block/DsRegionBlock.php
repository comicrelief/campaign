<?php

/**
 * @file
 * Contains \Drupal\ds_extras\Plugin\Block\DsRegionBlock.
 */

namespace Drupal\ds_extras\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides the region block plugin.
 *
 * @Block(
 *   id = "ds_region_block",
 *   admin_label = @Translation("Ds region block"),
 *   category = @Translation("Display Suite"),
 *   deriver = "Drupal\ds_extras\Plugin\Derivative\DsRegionBlock"
 * )
 */
class DsRegionBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $id = $this->getDerivativeId();
    $data = drupal_static('ds_block_region');

    if (!empty($data[$id])) {
      return array(
        '#markup' => drupal_render_children($data[$id]),
      );
    }
    else {
      return array();
    }
  }

}
