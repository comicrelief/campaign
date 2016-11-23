<?php

namespace Drupal\block_visibility_groups;

use Drupal\block_visibility_groups\Entity\BlockVisibilityGroup;

/**
 * Interface GroupEvaluatorInterface.
 *
 * @package Drupal\block_visibility_groups
 */
interface GroupEvaluatorInterface {

  /**
   * Evaluate Block Visibility Group.
   *
   * @param \Drupal\block_visibility_groups\Entity\BlockVisibilityGroup $block_visibility_group
   *
   * @return boolean
   */
  public function evaluateGroup(BlockVisibilityGroup $block_visibility_group);

}
