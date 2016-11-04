<?php

namespace Drupal\ds\Plugin\DsField;

/**
 * Defines a generic dynamic code field.
 *
 * @DsField(
 *   id = "dynamic_token_field",
 *   deriver = "Drupal\ds\Plugin\Derivative\DynamicTokenField"
 * )
 */
class DynamicTokenField extends TokenBase {

  /**
   * {@inheritdoc}
   */
  public function content() {
    $definition = $this->getPluginDefinition();
    return $definition['properties']['content']['value'];
  }

  /**
   * {@inheritdoc}
   */
  public function format() {
    $definition = $this->getPluginDefinition();
    return $definition['properties']['content']['format'];
  }

}
