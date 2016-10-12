<?php

namespace Drupal\yamlform\Plugin\YamlFormElement;

/**
 * Provides a 'checkbox' element.
 *
 * @YamlFormElement(
 *   id = "checkbox",
 *   api = "https://api.drupal.org/api/drupal/core!lib!Drupal!Core!Render!Element!Checkbox.php/class/Checkbox",
 *   label = @Translation("Checkbox"),
 *   category = @Translation("Basic elements"),
 * )
 */
class Checkbox extends BooleanBase {

  /**
   * {@inheritdoc}
   */
  public function getDefaultProperties() {
    $properties = parent::getDefaultProperties();
    $properties['title_display'] = 'after';
    return $properties;
  }

}
