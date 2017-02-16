<?php

namespace Drupal\cr_meta_icons\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'Head Sign Up' block.
 *
 * @Block(
 *   id = "cr_meta_icons",
 *   admin_label = @Translation("Meta Icons block"),
 * )
 */
class MetaIconsBlock extends BlockBase {
  /**
   * {@inheritdoc}
   */
  public function build() {
    return array(
      '#type' => 'markup',
      '#markup' => 'This is meta icons block, Use block--cr-meta-icons.html.twig in your theme templates folder to override it',
    );
  }
}
