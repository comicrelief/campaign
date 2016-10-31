<?php

namespace Drupal\ds_test\Plugin\DsField;

use Drupal\ds\Plugin\DsField\DsFieldBase;

/**
 * Test field plugin that returns nothing.
 *
 * @DsField(
 *   id = "test_field_nothing",
 *   title = @Translation("Test field plugin that returns nothing"),
 *   entity_type = "node"
 * )
 */
class TestFieldNothing extends DsFieldBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
  }

}
