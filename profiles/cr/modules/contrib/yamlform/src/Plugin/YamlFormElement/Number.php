<?php

namespace Drupal\yamlform\Plugin\YamlFormElement;

/**
 * Provides a 'number' element.
 *
 * @YamlFormElement(
 *   id = "number",
 *   api = "https://api.drupal.org/api/drupal/core!lib!Drupal!Core!Render!Element!Number.php/class/Number",
 *   label = @Translation("Number"),
 *   category = @Translation("Advanced elements"),
 * )
 */
class Number extends NumericBase {

  /**
   * {@inheritdoc}
   */
  public function getDefaultProperties() {
    return parent::getDefaultProperties() + [
      'min' => '',
      'max' => '',
      'step' => '',
    ];
  }

}
