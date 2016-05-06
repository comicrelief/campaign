<?php

/**
 * @file
 * Contains \Drupal\yamlform\Plugin\YamlFormElement\MachineName.
 */

namespace Drupal\yamlform\Plugin\YamlFormElement;

use Drupal\yamlform\YamlFormElementBase;
use Drupal\yamlform\YamlFormSubmissionInterface;

/**
 * Provides a 'machine_name' element.
 *
 * @YamlFormElement(
 *   id = "machine_name",
 *   label = @Translation("Machine name")
 * )
 */
class MachineName extends YamlFormElementBase {

  /**
   * {@inheritdoc}
   */
  public function prepare(array &$element, YamlFormSubmissionInterface $yamlform_submission) {
    // Since all inputs are place under the $form['inputs'] we need to
    // prepend the 'input' container to the #machine_name source.
    if (isset($element['#machine_name']['source'])) {
      array_unshift($element['#machine_name']['source'], 'inputs');
    }
    else {
      $element['#machine_name']['source'] = ['inputs', 'label'];
    }

    // Set #exists callback to function that will always returns TRUE.
    // This will prevent error and arbitrary functions from being called.
    // @see \Drupal\Core\Render\Element\MachineName::validateMachineName.
    $element['#machine_name']['exists'] = [get_class($this), 'exists'];
  }

  /**
   * Exists callback for machine name that always returns TRUE.
   *
   * @return bool
   *   Always returns TRUE.
   */
  static public function exists() {
    return FALSE;
  }

}
