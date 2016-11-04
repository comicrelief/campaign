<?php

namespace Drupal\ds_test\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Block\BlockBase;

/**
 * Defines a Display suite test block.
 *
 * @Block(
 *   id = "ds_test_block",
 *   admin_label = @Translation("Display Suite Test Block"),
 *   category = @Translation("ds")
 * )
 */
class DsTestBlock extends BlockBase {

  const BODY_TEXT = 'Display suite test block.';

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    return AccessResult::allowedIf(\Drupal::state()->get('ds_test_block__access', FALSE));
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    $build[]['#markup'] = $this::BODY_TEXT;
    return $build;
  }

  public function getCacheMaxAge() {
    return 0;
  }

}
