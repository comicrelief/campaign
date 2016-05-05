<?php

/**
 * @file
 * Contains \Drupal\yamlform\Plugin\YamlFormElement\TableSelect.
 */

namespace Drupal\yamlform\Plugin\YamlFormElement;

use Drupal\yamlform\YamlFormSubmissionInterface;

/**
 * Provides a 'tableselect' element.
 *
 * @YamlFormElement(
 *   id = "tableselect",
 *   label = @Translation("Table select")
 * )
 */
class TableSelect extends OptionsBase {

  /**
   * {@inheritdoc}
   */
  public function prepare(array &$element, YamlFormSubmissionInterface $yamlform_submission) {
    // Add one column header is not #header is specified.
    if (!isset($element['#header'])) {
      $element['#header'] = [
        (isset($element['#title']) ? $element['#title'] : ''),
      ];
    }

    // Convert associative array of options into one column
    // row.
    if (isset($element['#options'])) {
      foreach ($element['#options'] as $options_key => $options_value) {
        if (is_string($options_value)) {
          $element['#options'][$options_key] = [
            ['value' => $options_value],
          ];
        }
      }
    }
    parent::prepare($element, $yamlform_submission);
  }

  /**
   * {@inheritdoc}
   */
  public function setDefaultValue(array &$element, $default_value) {
    $element['#default_value'] = array_combine($element['#default_value'], $element['#default_value']);
  }

}
