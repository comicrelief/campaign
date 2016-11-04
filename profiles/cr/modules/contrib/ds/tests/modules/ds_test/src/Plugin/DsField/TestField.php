<?php

namespace Drupal\ds_test\Plugin\DsField;

use Drupal\ds\Plugin\DsField\DsFieldBase;

/**
 * Test field plugin.
 *
 * @DsField(
 *   id = "test_field",
 *   title = @Translation("Test field plugin"),
 *   entity_type = "node"
 * )
 */
class TestField extends DsFieldBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    return array('#markup' => 'Test field plugin on node ' . $this->entity()->id());
  }

}
