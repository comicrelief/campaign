<?php

/**
 * @file
 * Contains \Drupal\yamlform\Plugin\YamlFormElement\Checkbox.
 */

namespace Drupal\yamlform\Plugin\YamlFormElement;

use Drupal\yamlform\YamlFormElementBase;

/**
 * Provides a 'checkbox' element.
 *
 * @YamlFormElement(
 *   id = "checkbox",
 *   label = @Translation("Checkbox")
 * )
 */
class Checkbox extends YamlFormElementBase {

  /**
   * {@inheritdoc}
   */
  public function formatText(array &$element, $value, array $options = []) {
    return $this->t('Yes');
  }

}
