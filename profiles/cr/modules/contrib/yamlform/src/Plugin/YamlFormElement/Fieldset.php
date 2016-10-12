<?php

namespace Drupal\yamlform\Plugin\YamlFormElement;

/**
 * Provides a 'fieldset' element.
 *
 * @YamlFormElement(
 *   id = "fieldset",
 *   api = "https://api.drupal.org/api/drupal/core!lib!Drupal!Core!Render!Element!Fieldset.php/class/Fieldset",
 *   label = @Translation("Fieldset"),
 *   category = @Translation("Containers"),
 * )
 */
class Fieldset extends ContainerBase {

  /**
   * {@inheritdoc}
   */
  public function getDefaultProperties() {
    return parent::getDefaultProperties() + [
      'open' => FALSE,
    ];
  }

}
