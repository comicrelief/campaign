<?php

namespace Drupal\ds_extras\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;

/**
 * Provides block region definitions for ds blocks.
 *
 * @see \Drupal\ds_extras\Plugin\block\block\DsRegionBlock
 */
class DsRegionBlock extends DeriverBase {

  /**
   * Implements \Drupal\Component\Plugin\Derivative\DerivativeInterface::getDerivativeDefinitions().
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    $region_blocks = \Drupal::config('ds_extras.settings')->get('region_blocks');

    if (empty($region_blocks)) {
      return $this->derivatives;
    }

    foreach ($region_blocks as $key => $block) {
      $this->derivatives[$key] = $base_plugin_definition;
      $this->derivatives[$key]['delta'] = $key;
      $this->derivatives[$key]['admin_label'] = $block['title'];
    }

    return $this->derivatives;
  }

}
