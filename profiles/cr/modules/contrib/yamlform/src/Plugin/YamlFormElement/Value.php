<?php

namespace Drupal\yamlform\Plugin\YamlFormElement;

/**
 * Provides a 'value' element.
 *
 * @YamlFormElement(
 *   id = "value",
 *   api = "https://api.drupal.org/api/drupal/core!lib!Drupal!Core!Render!Element!Value.php/class/Value",
 *   label = @Translation("Value"),
 *   category = @Translation("Advanced elements"),
 * )
 */
class Value extends TextBase {

  /**
   * {@inheritdoc}
   */
  public function getDefaultProperties() {
    return [
      'value' => '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getElementSelectorOptions(array $element) {
    return [];
  }

}
