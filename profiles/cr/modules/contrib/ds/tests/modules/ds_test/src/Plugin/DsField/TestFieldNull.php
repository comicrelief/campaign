<?php

/**
 * @file
 * Contains \Drupal\ds_test\Plugin\DsField\TestFieldNull.
 */

namespace Drupal\ds_test\Plugin\DsField;

use Drupal\ds\Plugin\DsField\DsFieldBase;

/**
 * Test field plugin that returns NULL.
 *
 * @DsField(
 *   id = "test_field_null",
 *   title = @Translation("Test field plugin that returns NULL"),
 *   entity_type = "node"
 * )
 */
class TestFieldNull extends DsFieldBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    return NULL;
  }

}
