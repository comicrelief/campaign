<?php

namespace Drupal\yamlform\Plugin\YamlFormElement;

use Drupal\yamlform\YamlFormSubmissionInterface;

/**
 * Provides a 'tableselect' element.
 *
 * @YamlFormElement(
 *   id = "tableselect",
 *   api = "https://api.drupal.org/api/drupal/core!lib!Drupal!Core!Render!Element!Tableselect.php/class/Tableselect",
 *   label = @Translation("Table select"),
 *   category = @Translation("Options elements"),
 *   multiple = TRUE,
 * )
 */
class TableSelect extends OptionsBase {

  /**
   * {@inheritdoc}
   */
  public function prepare(array &$element, YamlFormSubmissionInterface $yamlform_submission) {
    parent::prepare($element, $yamlform_submission);

    // Add .js-form.wrapper to fix #states handling.
    $element['#attributes']['class'][] = 'js-form-wrapper';

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

    $element['#element_validate'][] = [get_class($this), 'validateMultipleOptions'];
  }

  /**
   * {@inheritdoc}
   */
  public function setDefaultValue(array &$element) {
    if (isset($element['#default_value'])) {
      $element['#default_value'] = array_combine($element['#default_value'], $element['#default_value']);
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function getElementSelectorInputsOptions(array $element) {
    $selectors = $element['#options'];
    foreach ($selectors as $value => &$text) {
      $text .= ' [' . $this->t('Checkbox') . ']';
    }
    return $selectors;
  }

}
