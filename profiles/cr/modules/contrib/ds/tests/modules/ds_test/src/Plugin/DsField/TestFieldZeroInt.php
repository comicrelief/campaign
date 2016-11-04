<?php

namespace Drupal\ds_test\Plugin\DsField;

use Drupal\ds\Plugin\DsField\DsFieldBase;

/**
 * Test field plugin that returns zero as an integer.
 *
 * @DsField(
 *   id = "test_field_zero_int",
 *   title = @Translation("Test field plugin that returns zero as an integer"),
 *   entity_type = "node"
 * )
 */
class TestFieldZeroInt extends DsFieldBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    return array('#markup' => 0);
  }

}
