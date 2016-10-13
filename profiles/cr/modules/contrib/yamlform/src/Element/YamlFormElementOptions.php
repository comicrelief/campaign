<?php

namespace Drupal\yamlform\Element;

use Drupal\Component\Serialization\Yaml;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element\FormElement;
use Drupal\Core\Url;
use Drupal\yamlform\Entity\YamlFormOptions as YamlFormOptionsEntity;

/**
 * Provides a form element for managing form element options.
 *
 * This element is used by select, radios, checkboxes, and likert elements.
 *
 * @FormElement("yamlform_element_options")
 */
class YamlFormElementOptions extends FormElement {

  const CUSTOM_OPTION = '';

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = get_class($this);
    return [
      '#input' => TRUE,
      '#likert' => FALSE,
      '#process' => [
        [$class, 'processYamlFormElementOptions'],
        [$class, 'processAjaxForm'],
      ],
      '#theme_wrappers' => ['form_element'],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function valueCallback(&$element, $input, FormStateInterface $form_state) {
    if ($input === FALSE) {
      if (isset($element['#default_value'])) {
        if (is_string($element['#default_value'])) {
          return (YamlFormOptionsEntity::load($element['#default_value'])) ? $element['#default_value'] : [];
        }
        else {
          return $element['#default_value'];
        }
      }
      else {
        return [];
      }
    }
    elseif (!empty($input['options'])) {
      return $input['options'];
    }
    elseif (isset($input['custom']['options'])) {
      return $input['custom']['options'];
    }
    else {
      return [];
    }
  }

  /**
   * Processes a form element options element.
   */
  public static function processYamlFormElementOptions(&$element, FormStateInterface $form_state, &$complete_form) {
    $element['#tree'] = TRUE;

    // Predefined options.
    // @see (/admin/structure/yamlform/settings/options/manage)
    $options = [];
    $yamlform_options = YamlFormOptionsEntity::loadMultiple();
    foreach ($yamlform_options as $id => $yamlform_option) {
      // Filter likert options for answers to the likert element.
      if ($element['#likert'] && strpos($id, 'likert_') !== 0) {
        continue;
      }
      $options[$id] = $yamlform_option->label();
    }
    asort($options);

    $t_args = [
      '@type' => ($element['#likert']) ? t('answers') : t('options'),
      ':href' => Url::fromRoute('entity.yamlform_options.collection')->toString(),
    ];

    // Select options.
    $element['options'] = [
      '#type' => 'select',
      '#description' => t('Please select <a href=":href">predefined @type</a> or enter custom @type.', $t_args),
      '#options' => [
        self::CUSTOM_OPTION => t('Custom...'),
      ] + $options,
      '#attributes' => [
        'class' => ['js-' . $element['#id'] . '-options'],
      ],
      '#error_no_message' => TRUE,
      '#default_value' => (isset($element['#default_value']) && !is_array($element['#default_value'])) ? $element['#default_value'] : '',
    ];

    // Custom options.
    $element['custom'] = [
      '#type' => 'yamlform_options',
      '#title' => $element['#title'],
      '#title_display' => 'invisible',
      '#label' => ($element['#likert']) ? t('answer') : t('option'),
      '#labels' => ($element['#likert']) ? t('answers') : t('options'),
      '#states' => [
        'visible' => [
          'select.js-' . $element['#id'] . '-options' => ['value' => ''],
        ],
      ],
      '#error_no_message' => TRUE,
      '#default_value' => (isset($element['#default_value']) && !is_string($element['#default_value'])) ? $element['#default_value'] : [],
    ];

    $element['#element_validate'] = [[get_called_class(), 'validateYamlFormElementOptions']];

    return $element;
  }

  /**
   * Validates a form element options element.
   */
  public static function validateYamlFormElementOptions(&$element, FormStateInterface $form_state, &$complete_form) {
    $options_value = $element['options']['#value'];
    $custom_value = $element['custom']['#value'];
    $value = $options_value;
    if ($options_value == self::CUSTOM_OPTION) {
      try {
        $value = (is_string($custom_value)) ? Yaml::decode($custom_value) : $custom_value;
      }
      catch (\Exception $exception) {
        // Do nothing since the 'yamlform_codemirror' element will have already
        // captured the validation error.
      }
    }

    $has_access = (!isset($element['#access']) || $element['#access'] === TRUE);
    if ($element['#required'] && empty($value) && $has_access) {
      if (isset($element['#required_error'])) {
        $form_state->setError($element, $element['#required_error']);
      }
      elseif (isset($element['#title'])) {
        $form_state->setError($element, t('@name field is required.', ['@name' => $element['#title']]));
      }
      else {
        $form_state->setError($element);
      }
    }

    $form_state->setValueForElement($element['options'], NULL);
    $form_state->setValueForElement($element['custom'], NULL);
    $form_state->setValueForElement($element, $value);
  }

}
