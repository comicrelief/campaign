<?php

namespace Drupal\block_visibility_groups;

use Drupal\block\Entity\Block;
use Drupal\Core\Entity\EntityStorageInterface;

/**
 *
 */
trait BlockVisibilityLister {

  /**
   * Get Labels for groups.
   *
   * @return array
   */
  protected function getBlockVisibilityLabels(EntityStorageInterface $storage) {
    $block_visibility_groups = $storage->loadMultiple();
    $labels = [];
    foreach ($block_visibility_groups as $type) {

      $labels[$type->id()] = $type->label();
    }
    return $labels;
  }

  /**
   * @param \Drupal\block\Entity\Block $block
   *
   * @return string
   */
  protected function getGroupForBlock(Block $block) {
    /** @var ConditionPluginCollection $conditions */
    $conditions = $block->getVisibilityConditions();
    $config_block_visibility_group = '';
    if ($conditions->has('condition_group')) {
      $condition_config = $conditions->get('condition_group')
        ->getConfiguration();
      $config_block_visibility_group = $condition_config['block_visibility_group'];
      return $config_block_visibility_group;
    }
    return $config_block_visibility_group;
  }

}
