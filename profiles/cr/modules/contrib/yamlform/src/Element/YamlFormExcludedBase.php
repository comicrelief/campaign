<?php

/**
 * @file
 * Contains \Drupal\yamlform\Element\YamlFormExcludedBase.
 */

namespace Drupal\yamlform\Element;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element\FormElement;

/**
 * Provides a base form element for YAML form excluded inputs and columns.
 *
 * This element is just intended to capture all the business logic around
 * selecting excluded YAML form inputs which is used by the
 * EmailYamlFormHandler and the YamlFormResultsExportForm forms.
 */
abstract class YamlFormExcludedBase extends FormElement {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = get_class($this);
    return [
      '#input' => TRUE,
      '#process' => [
        [$class, 'processYamlFormExcluded'],
      ],
      '#yamlform' => NULL,
      '#theme_wrappers' => ['form_element'],
    ];
  }

  /**
   * Processes a YAML form inputs form element.
   */
  public static function processYamlFormExcluded(&$element, FormStateInterface $form_state, &$complete_form) {
    $options = static::getYamlFormExcludedOptions($element);

    $default_value = array_diff(array_keys($options), array_keys($element['#default_value'] ?: []));
    $element['#tree'] = TRUE;
    $element['#element_validate'] = [[get_called_class(), 'validateYamlFormExcluded']];

    $element['tableselect'] = [
      '#type' => 'tableselect',
      '#header' => static::getYamlFormExcludedHeader(),
      '#options' => $options,
      '#js_select' => TRUE,
      '#empty' => t('No inputs are available.'),
      '#default_value' => array_combine($default_value, $default_value),
    ];

    // Build tableselect element with selected properties.
    $properties = [
      '#title',
      '#title_display',
      '#description',
      '#description_display',
      '#ajax',
    ];
    $element['tableselect'] += array_intersect_key($element, array_combine($properties, $properties));
    return $element;
  }

  /**
   * Validates a checkboxes other element.
   */
  public static function validateYamlFormExcluded(array &$element, FormStateInterface $form_state, &$complete_form) {
    $value = array_filter($element['tableselect']['#value']);

    // Converted value to excluded inputs.
    $options = array_keys($element['tableselect']['#options']);
    $excluded = array_diff($options, $value);

    // Unset tableselect and set the element's value to excluded.
    $form_state->setValueForElement($element['tableselect'], NULL);
    $form_state->setValueForElement($element, array_combine($excluded, $excluded));

    return $element;
  }

  /**
   * Get options for excluded tableselect element.
   *
   * @param array $element
   *   An associative array containing the properties and children of the
   *   generic input element.
   *
   * @return array
   *   An array of options containing title, name, and type of items for a
   *   tableselect element.
   */
  public static function getYamlFormExcludedOptions(array &$element) {
    /** @var \Drupal\yamlform\YamlFormInterface $yamlform */
    $yamlform = $element['#yamlform'];

    $options = [];
    $inputs = $yamlform->getFlattenedInputs();
    foreach ($inputs as $key => $input) {
      if (empty($input['#type']) || in_array($input['#type'], ['container', 'details', 'fieldset', 'item', 'label'])) {
        continue;
      }

      $options[$key] = [
        ['title' => isset($input['#title']) ? $input['#title'] : $key],
        ['name' => $key],
        ['type' => isset($input['#type']) ? $input['#type'] : ''],
      ];
    }
    return $options;
  }

  /**
   * Get header for the excluded tableselect element.
   *
   * @return array
   *   An array container the header for the excluded tableselect element.
   */
  public static function getYamlFormExcludedHeader() {
    return [t('Title'), t('Name'), t('Type')];
  }

}
