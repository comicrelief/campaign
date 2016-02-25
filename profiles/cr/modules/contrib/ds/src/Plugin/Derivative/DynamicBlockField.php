<?php

/**
 * @file
 * Contains \Drupal\ds\Plugin\Derivative\DynamicBlockField.
 */

namespace Drupal\ds\Plugin\Derivative;

use Drupal\ds\Form\BlockFieldForm;

/**
 * Retrieves dynamic block field plugin definitions.
 */
class DynamicBlockField extends DynamicField {

  /**
   * {@inheritdoc}
   */
  protected function getType() {
    return BlockFieldForm::TYPE;
  }

}
