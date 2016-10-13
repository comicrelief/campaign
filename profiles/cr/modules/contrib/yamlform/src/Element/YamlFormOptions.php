<?php

namespace Drupal\yamlform\Element;

use Drupal\Component\Utility\NestedArray;
use Drupal\Component\Utility\Unicode;
use Drupal\Component\Serialization\Yaml;
use Drupal\Core\Render\Element;
use Drupal\Core\Render\Element\FormElement;
use Drupal\Core\Form\FormStateInterface;
use Drupal\yamlform\Utility\YamlFormElementHelper;

/**
 * Provides a form element to assist in creation of options.
 *
 * This provides a nicer interface for non-technical users to add values and
 * labels for options, possible within option groups.
 *
 * @FormElement("yamlform_options")
 */
class YamlFormOptions extends FormElement {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = get_class($this);
    return [
      '#input' => TRUE,
      '#label' => t('option'),
      '#labels' => t('options'),
      '#empty_options' => 5,
      '#add_more' => 1,
      '#process' => [
        [$class, 'processOptions'],
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
        return (is_string($element['#default_value'])) ? Yaml::decode($element['#default_value']) : $element['#default_value'];
      }
      else {
        return [];
      }
    }
    elseif (is_array($input) && isset($input['options'])) {
      return (is_string($input['options'])) ? Yaml::decode($input['options']) : self::convertValuesToOptions($input['options']);
    }
    else {
      return NULL;
    }
  }

  /**
   * Process options and build options widget.
   */
  public static function processOptions(&$element, FormStateInterface $form_state, &$complete_form) {
    $element['#tree'] = TRUE;

    // Add validate callback that extracts the associative array of options.
    $element['#element_validate'] = [[get_called_class(), 'validateOptions']];

    // Wrap this $element in a <div> that handle #states.
    YamlFormElementHelper::fixStatesWrapper($element);

    // For options with optgroup display a CodeMirror YAML editor.
    if (isset($element['#default_value']) && is_array($element['#default_value']) && self::hasOptGroup($element['#default_value'])) {
      // Build table.
      $element['options'] = [
        '#type' => 'yamlform_codemirror',
        '#mode' => 'yaml',
        '#default_value' => Yaml::encode($element['#default_value']),
        '#description' => t('Key-value pairs MUST be specified as "safe_key: \'Some readable options\'". Use of only alphanumeric characters and underscores is recommended in keys. One option per line.') . '<br/>' .
        t('Option groups can be created by using just the group name followed by indented group options.'),
      ];
      return $element;
    }

    // Get unique key used to store the current number of options.
    $number_of_options_storage_key = self::getStorageKey($element, 'number_of_options');

    // Store the number of options which is the number of
    // #default_values + number of empty_options.
    if ($form_state->get($number_of_options_storage_key) === NULL) {
      if (empty($element['#default_value']) || !is_array($element['#default_value'])) {
        $number_of_default_values = 0;
        $number_of_empty_options = (int) $element['#empty_options'];
      }
      else {
        $number_of_default_values = count($element['#default_value']);
        $number_of_empty_options = 1;
      }

      $form_state->set($number_of_options_storage_key, $number_of_default_values + $number_of_empty_options);
    }

    $number_of_options = $form_state->get($number_of_options_storage_key);

    $table_id = implode('_', $element['#parents']) . '_table';
    // DEBUG: Disable AJAX callback by commenting out the below callback and
    // wrapper.
    $ajax_settings = [
      'callback' => [get_called_class(), 'ajaxCallback'],
      'wrapper' => $table_id,
    ];

    // Build header.
    $t_args = ['@label' => Unicode::ucfirst($element['#label'])];
    $header = [
      t('@label value', $t_args),
      t('@label text', $t_args),
      t('Weight'),
      '',
    ];

    // Build rows.
    $row_index = 0;
    $weight = 0;
    $rows = [];
    if (!$form_state->isRebuilding() && isset($element['#default_value']) && is_array($element['#default_value'])) {
      foreach ($element['#default_value'] as $value => $label) {
        $rows[$row_index] = self::buildOptionRow($table_id, $row_index, $value, $label, $weight++, $ajax_settings);
        $row_index++;
      }
    }
    while ($row_index < $number_of_options) {
      $rows[$row_index] = self::buildOptionRow($table_id, $row_index, '', '', $weight++, $ajax_settings);
      $row_index++;
    }

    // Build table.
    $element['options'] = [
      '#prefix' => '<div id="' . $table_id . '" class="yamlform-options-table">',
      '#suffix' => '</div>',
      '#type' => 'table',
      '#header' => $header,
      '#tabledrag' => [
        [
          'action' => 'order',
          'relationship' => 'sibling',
          'group' => 'yamlform-options-sort-weight',
        ],
      ],
    ] + $rows;

    // Build add options actions.
    $element['add'] = [
      '#prefix' => '<div class="container-inline">',
      '#suffix' => '</div>',
    ];
    $element['add']['submit'] = [
      '#type' => 'submit',
      '#value' => t('Add'),
      '#limit_validation_errors' => [],
      '#submit' => [[get_called_class(), 'addOptionsSubmit']],
      '#ajax' => $ajax_settings,
      '#name' => $table_id . '_add',
    ];
    $element['add']['more_options'] = [
      '#type' => 'number',
      '#min' => 1,
      '#max' => 100,
      '#default_value' => $element['#add_more'],
      '#field_suffix' => t('more @labels', ['@labels' => $element['#labels']]),
    ];

    $element['#attached']['library'][] = 'yamlform/yamlform.element.options';

    return $element;
  }

  /**
   * Build an option's row that contains the options value, text, and weight.
   *
   * @param string $table_id
   *   The option element's table id.
   * @param int $row_index
   *   The option's row index.
   * @param string $value
   *   The option's value.
   * @param string $text
   *   The option's text.
   * @param int $weight
   *   The option's weight.
   * @param array $ajax_settings
   *   An array containing AJAX callback settings.
   *
   * @return array
   *   A render array containing inputs for an option's value, text, and weight.
   */
  public static function buildOptionRow($table_id, $row_index, $value, $text, $weight, array $ajax_settings) {
    $row = [];
    $row['value'] = [
      '#type' => 'textfield',
      '#title' => t('Option value'),
      '#title_display' => 'invisible',
      '#size' => 25,
      '#placeholder' => t('Enter value'),
      '#default_value' => $value,
    ];
    $row['text'] = [
      '#type' => 'textfield',
      '#title' => t('Option text'),
      '#title_display' => 'invisible',
      '#size' => 25,
      '#maxlength' => NULL,
      '#placeholder' => t('Enter text'),
      '#default_value' => $text,
    ];
    $row['weight'] = [
      '#type' => 'weight',
      '#delta' => 1000,
      '#title' => t('Option weight'),
      '#title_display' => 'invisible',
      '#attributes' => [
        'class' => ['yamlform-options-sort-weight'],
      ],
      '#default_value' => $weight,
    ];

    $row['remove'] = [
      '#type' => 'image_button',
      '#src' => 'core/misc/icons/787878/ex.svg',
      '#limit_validation_errors' => [],
      '#submit' => [[get_called_class(), 'removeOptionSubmit']],
      '#ajax' => $ajax_settings,
      // Issue #1342066 Document that buttons with the same #value need a unique
      // #name for the form API to distinguish them, or change the form API to
      // assign unique #names automatically.
      '#row_index' => $row_index,
      '#name' => $table_id . '_remove_' . $row_index,
    ];

    $row['#weight'] = $weight;
    $row['#attributes']['class'][] = 'draggable';
    return $row;
  }

  /****************************************************************************/
  // Callbacks.
  /****************************************************************************/

  /**
   * Form submission handler for adding more options.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public static function addOptionsSubmit(array &$form, FormStateInterface $form_state) {
    // Get the form options element by going up two levels.
    $button = $form_state->getTriggeringElement();
    $element =& NestedArray::getValue($form, array_slice($button['#array_parents'], 0, -2));

    // Add more options to the number of options.
    $number_of_options_storage_key = self::getStorageKey($element, 'number_of_options');
    $number_of_options = $form_state->get($number_of_options_storage_key);
    $more_options = (int) $element['add']['more_options']['#value'];
    $form_state->set($number_of_options_storage_key, $number_of_options + $more_options);

    // Reset values.
    $element['options']['#value'] = array_values($element['options']['#value']);
    $form_state->setValueForElement($element['options'], $element['options']['#value']);
    NestedArray::setValue($form_state->getUserInput(), $element['options']['#parents'], $element['options']['#value']);

    // Rebuild the form.
    $form_state->setRebuild();
  }

  /**
   * Form submission handler for removing an option.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public static function removeOptionSubmit(array &$form, FormStateInterface $form_state) {
    $button = $form_state->getTriggeringElement();
    $element = NestedArray::getValue($form, array_slice($button['#array_parents'], 0, -3));

    // Remove options.
    unset($element['options']['#value'][$button['#row_index']]);

    // Remove one option from the 'number of options'.
    $number_of_options_storage_key = self::getStorageKey($element, 'number_of_options');
    $number_of_options = $form_state->get($number_of_options_storage_key);
    // Never allow the number of options to be less than 1.
    if ($number_of_options != 1) {
      $form_state->set($number_of_options_storage_key, $number_of_options - 1);
    }

    // Reset values.
    $element['options']['#value'] = array_values($element['options']['#value']);
    $form_state->setValueForElement($element['options'], $element['options']['#value']);
    NestedArray::setValue($form_state->getUserInput(), $element['options']['#parents'], $element['options']['#value']);

    // Rebuild the form.
    $form_state->setRebuild();
  }

  /**
   * Form submission AJAX callback the returns the options table.
   */
  public static function ajaxCallback(array &$form, FormStateInterface $form_state) {
    $button = $form_state->getTriggeringElement();
    $parent_length = (isset($button['#row_index'])) ? -3 : -2;
    $element = NestedArray::getValue($form, array_slice($button['#array_parents'], 0, $parent_length));
    return $element['options'];
  }

  /**
   * Validates form options element.
   */
  public static function validateOptions(&$element, FormStateInterface $form_state, &$complete_form) {
    if (isset($element['options']['#value']) && is_string($element['options']['#value'])) {
      $options = Yaml::decode($element['options']['#value']);
    }
    else {
      // Collect the option values in a sortable array.
      $values = [];
      foreach (Element::children($element['options']) as $child_key) {
        $row = $element['options'][$child_key];
        $values[] = [
          'value' => $row['value']['#value'],
          'text' => $row['text']['#value'],
          'weight' => $row['weight']['#value'],
        ];
      }

      // Sort the option values.
      uasort($values, ['Drupal\Component\Utility\SortArray', 'sortByWeightElement']);

      // Convert values to options.
      $options = self::convertValuesToOptions($values);
    }

    // Validate required options.
    if (!empty($element['#required']) && empty($options)) {
      if (isset($element['#required_error'])) {
        $form_state->setError($element, $element['#required_error']);
      }
      elseif (isset($element['#title'])) {
        $form_state->setError($element, t('@name field is required.', ['@name' => $element['#title']]));
      }
      else {
        $form_state->setError($element);
      }
      return;
    }

    // Clear the element's value by setting it to NULL.
    $form_state->setValueForElement($element, NULL);

    // Now, set the sorted options as the element's value.
    $form_state->setValueForElement($element, $options);
  }

  /****************************************************************************/
  // Helper functions.
  /****************************************************************************/

  /**
   * Get unique key used to store the number of options for an element.
   *
   * @param array $element
   *   An element.
   *
   * @return string
   *   A unique key used to store the number of options for an element.
   */
  public static function getStorageKey(array $element, $name) {
    return 'yamlform_options__' . $element['#name'] . '__' . $name;
  }

  /**
   * Convert an array containing of option value, text, and weight to an associative array of options.
   *
   * @param array $values
   *   An array containing of option value, text, and weight.
   *
   * @return array
   *   An associative array of options.
   */
  public static function convertValuesToOptions(array $values = []) {
    // Sort the option values.
    uasort($values, ['Drupal\Component\Utility\SortArray', 'sortByWeightElement']);

    // Now build the associative array of options.
    $options = [];
    foreach ($values as $value) {
      $option_value = $value['value'];
      $option_text = $value['text'];
      // Skip completely empty options.
      if ($option_value === '' && $option_text === '') {
        continue;
      }

      // Populate empty option value or option text.
      if ($option_value === '') {
        $option_value = $option_text;
      }
      elseif ($option_text === '') {
        $option_text = $option_value;
      }

      $options[$option_value] = $option_text;
    }

    return $options;
  }

  /**
   * Determine if options array contains an OptGroup.
   *
   * @param array $options
   *   An array of options.
   *
   * @return bool
   *   TRUE if options array contains an OptGroup.
   */
  public static function hasOptGroup(array $options) {
    foreach ($options as $option_text) {
      if (is_array($option_text)) {
        return TRUE;
      }
    }
    return FALSE;
  }

}
