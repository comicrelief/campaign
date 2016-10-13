<?php

namespace Drupal\yamlform\Element;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element\FormElement;
use Drupal\Core\Render\Element\CompositeFormElementTrait;
use Drupal\yamlform\Entity\YamlFormOptions as YamlFormOptionsEntity;

/**
 * Provides an base composite form element.
 */
abstract class YamlFormCompositeBase extends FormElement {

  use CompositeFormElementTrait;

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = get_class($this);
    return [
      '#input' => TRUE,
      '#process' => [
        [$class, 'processYamlFormComposite'],
        [$class, 'processAjaxForm'],
      ],
      '#pre_render' => [
        [$class, 'preRenderCompositeFormElement'],
      ],
      '#theme' => str_replace('yamlform_', 'yamlform_composite_', $this->getPluginId()),
      '#theme_wrappers' => ['container'],
      '#required' => FALSE,
      '#flexbox' => TRUE,
    ];
  }

  /**
   * Get a renderable array of form elements.
   *
   * @return array
   *   A renderable array of form elements, containing the base properties
   *   for the composite's form elements.
   */
  public static function getCompositeElements() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public static function preRenderCompositeFormElement($element) {
    $element = CompositeFormElementTrait::preRenderCompositeFormElement($element);

    // Add class name to wrapper attributes.
    $class_name = str_replace('_', '-', $element['#type']);
    $element['#attributes']['class'][] = 'js-' . $class_name;
    $element['#attributes']['class'][] = $class_name;

    return $element;
  }

  /**
   * Processes a composite form element.
   */
  public static function processYamlFormComposite(&$element, FormStateInterface $form_state, &$complete_form) {
    if (isset($element['#initialize'])) {
      return $element;
    }

    $element['#initialize'] = TRUE;
    $element['#tree'] = TRUE;
    $composite_elements = static::getCompositeElements();
    foreach ($composite_elements as $composite_key => &$composite_element) {
      // Transfer '#{composite_key}_{property}' from main element to composite
      // element.
      foreach ($element as $property_key => $property_value) {
        if (strpos($property_key, '#' . $composite_key . '__') === 0) {
          $composite_property_key = str_replace('#' . $composite_key . '__', '#', $property_key);
          $composite_element[$composite_property_key] = $property_value;
        }
      }

      if (isset($element['#value'][$composite_key])) {
        $composite_element['#value'] = $element['#value'][$composite_key];
      }

      // Always set #access which is used to hide/show the elements container.
      $composite_element += [
        '#access' => TRUE,
      ];

      // Never required hidden composite elements.
      if ($composite_element['#access'] == FALSE) {
        unset($composite_element['#required']);
      }

      // Load options.
      if (isset($composite_element['#options'])) {
        $composite_element['#options'] = YamlFormOptionsEntity::getElementOptions($composite_element);
      }

      // Handle #type specific customizations.
      if (isset($composite_element['#type'])) {
        switch ($composite_element['#type']) {
          case 'select':
          case 'yamlform_select_other':
            // Always include an empty option, even if the composite element
            // is not required.
            // @see https://api.drupal.org/api/drupal/core!lib!Drupal!Core!Render!Element!Select.php/class/Select/8.2.x
            // Use placeholder as empty option.
            if (!isset($composite_element['#empty_option'])) {
              if (isset($composite_element['#placeholder'])) {
                $composite_element['#empty_option'] = $composite_element['#placeholder'];
              }
              elseif (empty($composite_element['#required'])) {
                $composite_element['#empty_option'] = t('- None -');
              }
            }
            break;
        }
      }
    }

    $element += $composite_elements;
    $element['#element_validate'] = [[get_called_class(), 'validateYamlFormComposite']];

    if (!empty($element['#flexbox'])) {
      $element['#attached']['library'][] = 'yamlform/yamlform.element.flexbox';
    }

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public static function valueCallback(&$element, $input, FormStateInterface $form_state) {
    $composite_elements = static::getCompositeElements();
    $default_value = [];
    foreach ($composite_elements as $composite_key => $composite_element) {
      if (isset($composite_element['#type']) && $composite_element['#type'] != 'label') {
        $default_value[$composite_key] = '';
      }
    }

    if ($input === FALSE) {
      if (empty($element['#default_value']) || !is_array($element['#default_value'])) {
        $element['#default_value'] = [];
      }
      return $element['#default_value'] + $default_value;
    }
    return $input + $default_value;
  }

  /**
   * Validates a composite element.
   */
  public static function validateYamlFormComposite(&$element, FormStateInterface $form_state, &$complete_form) {
    $value = $element['#value'];

    // Validate required composite elements.
    $composite_elements = static::getCompositeElements();
    foreach ($composite_elements as $composite_key => $composite_element) {
      if (!empty($element[$composite_key]['#required']) && $value[$composite_key] == '') {
        if (isset($element[$composite_key]['#title'])) {
          $form_state->setError($element[$composite_key], t('@name field is required.', ['@name' => $element[$composite_key]['#title']]));
        }
      }
    }
  }

}
