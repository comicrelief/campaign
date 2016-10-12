<?php

namespace Drupal\yamlform\Element;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element\FormElement;

/**
 * Provides a base form element for form excluded elements and columns.
 *
 * This element is just intended to capture all the business logic around
 * selecting excluded form elements which is used by the
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
   * Processes a form elements form element.
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
      '#empty' => t('No elements are available.'),
      '#default_value' => array_combine($default_value, $default_value),
    ];

    // Build tableselect element with selected properties.
    $properties = [
      '#title',
      '#title_display',
      '#description',
      '#description_display',
      '#ajax',
      '#states',
    ];
    $element['tableselect'] += array_intersect_key($element, array_combine($properties, $properties));
    return $element;
  }

  /**
   * Validates a tablelselect element.
   */
  public static function validateYamlFormExcluded(array &$element, FormStateInterface $form_state, &$complete_form) {
    $value = array_filter($element['tableselect']['#value']);

    // Converted value to excluded elements.
    $options = array_keys($element['tableselect']['#options']);
    $excluded = array_diff($options, $value);

    // Unset tableselect and set the element's value to excluded.
    $form_state->setValueForElement($element['tableselect'], NULL);
    $form_state->setValueForElement($element, array_combine($excluded, $excluded));
  }

  /**
   * Get options for excluded tableselect element.
   *
   * @param array $element
   *   An associative array containing the properties and children of the
   *   generic element element.
   *
   * @return array
   *   An array of options containing title, name, and type of items for a
   *   tableselect element.
   */
  public static function getYamlFormExcludedOptions(array $element) {
    /** @var \Drupal\yamlform\YamlFormInterface $yamlform */
    $yamlform = $element['#yamlform'];

    $options = [];
    $elements = $yamlform->getElementsInitializedAndFlattened();
    foreach ($elements as $key => $element) {
      if (empty($element['#type']) || in_array($element['#type'], ['container', 'details', 'fieldset', 'item', 'label'])) {
        continue;
      }

      $options[$key] = [
        ['title' => $element['#admin_title'] ?:$element['#title'] ?: $key],
        ['name' => $key],
        ['type' => isset($element['#type']) ? $element['#type'] : ''],
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
