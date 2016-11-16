<?php

namespace Drupal\ds_test\Plugin\DsField;

use Drupal\ds\Plugin\DsField\DsFieldBase;

/**
 * Test field plugin.
 *
 * @DsField(
 *   id = "test_multiple_field",
 *   title = @Translation("Test multiple field plugin"),
 *   entity_type = "node"
 * )
 */
class TestMultipleField extends DsFieldBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    return array(
      0 => array(
        '#markup' => 'Test row one of multiple field plugin on node ' . $this->entity()->id(),
      ),
      1 => array(
        '#markup' => 'Test row two of multiple field plugin on node ' . $this->entity()->id(),
      ),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function isMultiple() {
    return TRUE;
  }

}
