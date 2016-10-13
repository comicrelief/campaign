<?php

namespace Drupal\yamlform\Plugin\YamlFormElement;

use Drupal\yamlform\YamlFormElementBase;
use Drupal\yamlform\YamlFormSubmissionInterface;

/**
 * Provides a 'machine_name' element.
 *
 * @YamlFormElement(
 *   id = "machine_name",
 *   api = "https://api.drupal.org/api/drupal/core!lib!Drupal!Core!Render!Element!MachineName.php/class/MachineName",
 *   label = @Translation("Machine name"),
 *   hidden = TRUE,
 * )
 */
class MachineName extends YamlFormElementBase {

  /**
   * {@inheritdoc}
   */
  public function prepare(array &$element, YamlFormSubmissionInterface $yamlform_submission) {
    parent::prepare($element, $yamlform_submission);
    // Since all elements are place under the $form['elements'] we need to
    // prepend the 'element' container to the #machine_name source.
    if (isset($element['#machine_name']['source'])) {
      array_unshift($element['#machine_name']['source'], 'elements');
    }
    else {
      $element['#machine_name']['source'] = ['elements', 'label'];
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
