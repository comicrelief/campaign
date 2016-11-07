<?php

namespace Drupal\ds\Plugin\Derivative;

use Drupal\ds\Form\TokenFieldForm;

/**
 * Retrieves dynamic code field plugin definitions.
 */
class DynamicTokenField extends DynamicField {

  /**
   * {@inheritdoc}
   */
  protected function getType() {
    return TokenFieldForm::TYPE;
  }

}
