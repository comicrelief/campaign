<?php

namespace Drupal\example_field\Plugin\DsField;

use Drupal\Core\Form\FormStateInterface;
use Drupal\ds\Plugin\DsField\DsFieldBase;

/**
 * Generated field.
 *
 * @DsField(
 *   id = "example_field_ExampleField",
 *   title = @Translation("ExampleField"),
 *   entity_type = "node"
 * )
 */
class ExampleField extends DsFieldBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    return array();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm($form, FormStateInterface $form_state) {
    return array();
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return array();
  }

  /**
   * {@inheritdoc}
   */
  public function formatters() {
    return array();
  }

  /**
   * {@inheritdoc}
   */
  public function isAllowed() {
    return TRUE;
  }

}
