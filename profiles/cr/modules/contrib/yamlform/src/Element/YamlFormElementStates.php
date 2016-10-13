<?php

namespace Drupal\yamlform\Element;

use Drupal\Component\Utility\NestedArray;
use Drupal\Component\Serialization\Yaml;
use Drupal\Component\Utility\Unicode;
use Drupal\Core\Render\Element\FormElement;
use Drupal\Core\Form\FormStateInterface;
use Drupal\yamlform\Utility\YamlFormArrayHelper;
use Drupal\yamlform\Utility\YamlFormTidy;

/**
 * Provides a form element to edit an element's #states.
 *
 * @FormElement("yamlform_element_states")
 */
class YamlFormElementStates extends FormElement {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = get_class($this);
    return [
      '#input' => TRUE,
      '#selector_options' => [],
      '#empty_states' => 3,
      '#process' => [
        [$class, 'processStates'],
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
          $default_value = Yaml::decode($element['#default_value']);
        }
        else {
          $default_value = $element['#default_value'] ?: [];
        }
        return self::convertFormApiStatesToStatesArray($default_value);
      }
      else {
        return [];
      }
    }
    elseif (is_array($input) && isset($input['states'])) {
      return (is_string($input['states'])) ? Yaml::decode($input['states']) : self::convertFormValuesToStatesArray($input['states']);
    }
    else {
      return [];
    }
  }

  /**
   * Expand an email confirm field into two HTML5 email elements.
   */
  public static function processStates(&$element, FormStateInterface $form_state, &$complete_form) {
    // Define default #state_options and #trigger_options.
    // There are also defined by \Drupal\yamlform\YamlFormElementBase::form.
    $element += [
      '#state_options' => [
        'enabled' => t('Enabled'),
        'disabled' => t('Disabled'),
        'required' => t('Required'),
        'optional' => t('Optional'),
        'visible' => t('Visible'),
        'invisible' => t('Invisible'),
        'checked' => t('Checked'),
        'unchecked' => t('Unchecked'),
        'expanded' => t('Expanded'),
        'collapsed' => t('Collapsed'),
      ],
      '#trigger_options' => [
        'empty' => t('Empty'),
        'filled' => t('Filled'),
        'checked' => t('Checked'),
        'unchecked' => t('Unchecked'),
        'expanded' => t('Expanded'),
        'collapsed' => t('Collapsed'),
        'value' => t('Value is'),
      ],
    ];

    $element['#tree'] = TRUE;

    // Add validate callback that extracts the associative array of states.
    $element['#element_validate'] = [[get_called_class(), 'validateStates']];

    // For customized #states display a CodeMirror YAML editor.
    if ($warning_message = self::isDefaultValueCustomizedFormApiStates($element)) {
      $warning_message .= ' ' . t('Form API #states must be manually entered.');
      $element['messages'] = [
        '#type' => 'container',
        '#attributes' => ['class' => ['messages', 'messages--warning']],
        'warning' => ['#markup' => $warning_message],
      ];
      $element['states'] = [
        '#type' => 'yamlform_codemirror',
        '#mode' => 'yaml',
        '#default_value' => YamlFormTidy::tidy(Yaml::encode($element['#default_value'])),
        '#description' => t('Learn more about Drupal\'s <a href=":href">Form API #states</a>.', [':href' => 'https://www.lullabot.com/articles/form-api-states']),
      ];
      return $element;
    }

    $table_id = implode('_', $element['#parents']) . '_table';

    // Store the number of rows.
    $storage_key = self::getStorageKey($element, 'number_of_rows');
    if ($form_state->get($storage_key) === NULL) {
      if (empty($element['#default_value']) || !is_array($element['#default_value'])) {
        $number_of_rows = 2;
      }
      else {
        $number_of_rows = count($element['#default_value']);
      }
      $form_state->set($storage_key, $number_of_rows);
    }
    $number_of_rows = $form_state->get($storage_key);

    // DEBUG: Disable AJAX callback by commenting out the below callback and
    // wrapper.
    $ajax_settings = [
      'callback' => [get_called_class(), 'ajaxCallback'],
      'wrapper' => $table_id,
    ];

    // Build header.
    $header = [
      ['data' => t('State'), 'width' => '20%'],
      ['data' => t('Element/Selector'), 'width' => '30%'],
      ['data' => t('Trigger'), 'width' => '20%'],
      ['data' => t('Value'), 'width' => '10%'],
      ['data' => '', 'width' => '10%'],
    ];

    // Get states and number of rows.
    if (($form_state->isRebuilding())) {
      $states = $element['#value'];
    }
    else {
      $states = (isset($element['#default_value'])) ? self::convertFormApiStatesToStatesArray($element['#default_value']) : [];
    }

    // Build state and conditions rows.
    $row_index = 0;
    $rows = [];
    foreach ($states as $state => $state_settings) {
      $rows[$row_index] = self::buildStateRow($element, $state_settings, $table_id, $row_index, $ajax_settings);
      $row_index++;
      foreach ($state_settings['conditions'] as $condition) {
        $rows[$row_index] = self::buildConditionRow($element, $condition, $table_id, $row_index, $ajax_settings);
        $row_index++;
      }
    }

    // Generator empty state with conditions rows.
    if ($row_index < $number_of_rows) {
      $rows[$row_index] = self::buildStateRow($element, [], $table_id, $row_index, $ajax_settings);;
      $row_index++;
      while ($row_index < $number_of_rows) {
        $rows[$row_index] = self::buildConditionRow($element, [], $table_id, $row_index, $ajax_settings);
        $row_index++;
      }
    }

    // Build table.
    $element['states'] = [
      '#prefix' => '<div id="' . $table_id . '" class="yamlform-states-table">',
      '#suffix' => '</div>',
      '#type' => 'table',
      '#header' => $header,
    ] + $rows;

    // Build add state action.
    $element['add'] = [
      '#type' => 'submit',
      '#value' => t('Add another state'),
      '#limit_validation_errors' => [],
      '#submit' => [[get_called_class(), 'addStateSubmit']],
      '#ajax' => $ajax_settings,
      '#name' => $table_id . '_add',
    ];

    $element['#attached']['library'][] = 'yamlform/yamlform.element.states';

    return $element;
  }

  /**
   * Build state row.
   *
   * @param array $element
   *   The form element.
   * @param array $state
   *   The state.
   * @param string $table_id
   *   The element's table id.
   * @param int $row_index
   *   The row index.
   * @param array $ajax_settings
   *   An array containing AJAX callback settings.
   *
   * @return array
   *   A render array containing a state table row.
   */
  protected static function buildStateRow(array $element, array $state, $table_id, $row_index, array $ajax_settings) {
    $state += ['state' => '', 'operator' => 'and'];
    $row = [
      '#attributes' => [
        'class' => ['yamlform-states-table--state'],
      ],
    ];
    $row['state'] = [
      '#type' => 'select',
      '#options' => $element['#state_options'],
      '#default_value' => $state['state'],
      '#empty_option' => '',
      '#empty_value' => '',
    ];
    $row['operator'] = [
      '#type' => 'select',
      '#options' => [
        'and' => t('All'),
        'or' => t('Any'),
      ],
      '#default_value' => $state['operator'],
      '#field_prefix' => t('if'),
      '#field_suffix' => t('of the following is met:'),
      '#wrapper_attributes' => ['colspan' => 3, 'align' => 'left'],
    ];
    $row['operations'] = self::buildOperations($table_id, $row_index, $ajax_settings);
    return $row;
  }

  /**
   * Build condition row.
   *
   * @param array $element
   *   The form element.
   * @param array $condition
   *   The condition.
   * @param string $table_id
   *   The element's table id.
   * @param int $row_index
   *   The row index.
   * @param array $ajax_settings
   *   An array containing AJAX callback settings.
   *
   * @return array
   *   A render array containing a condition table row.
   */
  protected static function buildConditionRow(array $element, array $condition, $table_id, $row_index, array $ajax_settings) {
    $condition += ['selector' => '', 'trigger' => '', 'value' => ''];

    $element_name = $element['#name'];
    $trigger_selector = ":input[name=\"{$element_name}[states][{$row_index}][trigger]\"]";

    $row = [
      '#attributes' => [
        'class' => ['yamlform-states-table--condition'],
      ],
    ];
    $row['state'] = [];
    $row['selector'] = [
      '#type' => 'yamlform_select_other',
      '#options' => $element['#selector_options'],
      '#default_value' => $condition['selector'],
      '#empty_option' => '',
      '#empty_value' => '',
    ];
    $row['trigger'] = [
      '#type' => 'select',
      '#options' => $element['#trigger_options'],
      '#default_value' => $condition['trigger'],
      '#empty_option' => '',
      '#empty_value' => '',
    ];
    $row['value'] = [
      '#type' => 'textfield',
      '#title' => t('Value'),
      '#title_display' => 'invisible',
      '#size' => 25,
      '#default_value' => $condition['value'],
      '#states' => [
        'visible' => [
          $trigger_selector => ['value' => 'value'],
        ],
      ],
    ];
    $row['operations'] = self::buildOperations($table_id, $row_index, $ajax_settings);
    return $row;
  }

  /**
   * Build a state's operations.
   *
   * @param string $table_id
   *   The option element's table id.
   * @param int $row_index
   *   The option's row index.
   * @param array $ajax_settings
   *   An array containing AJAX callback settings.
   *
   * @return array
   *   A render array containing state operations..
   */
  protected static function buildOperations($table_id, $row_index, array $ajax_settings) {
    $operations = [];
    $operations['add'] = [
      '#type' => 'image_button',
      '#src' => drupal_get_path('module', 'yamlform') . '/images/icons/plus.svg',
      '#limit_validation_errors' => [],
      '#submit' => [[get_called_class(), 'addConditionSubmit']],
      '#ajax' => $ajax_settings,
      '#row_index' => $row_index,
      '#name' => $table_id . '_add_' . $row_index,
    ];
    $operations['remove'] = [
      '#type' => 'image_button',
      '#src' => drupal_get_path('module', 'yamlform') . '/images/icons/ex.svg',
      '#limit_validation_errors' => [],
      '#submit' => [[get_called_class(), 'removeRowSubmit']],
      '#ajax' => $ajax_settings,
      '#row_index' => $row_index,
      '#name' => $table_id . '_remove_' . $row_index,
    ];
    return $operations;
  }

  /****************************************************************************/
  // Callbacks.
  /****************************************************************************/

  /**
   * Form submission handler for adding another state.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public static function addStateSubmit(array &$form, FormStateInterface $form_state) {
    // Get the form states element by going up one level.
    $button = $form_state->getTriggeringElement();
    $element =& NestedArray::getValue($form, array_slice($button['#array_parents'], 0, -1));

    $values = $element['states']['#value'];

    // Add new state and condition.
    $values[] = [
      'state' => '',
      'operator' => 'and',
    ];
    $values[] = [
      'selector' => ['select' => '', 'other' => ''],
      'trigger' => '',
      'value' => '',
    ];

    // Update element's #value.
    $form_state->setValueForElement($element['states'], $values);
    NestedArray::setValue($form_state->getUserInput(), $element['states']['#parents'], $values);

    // Update the number of rows.
    $form_state->set(self::getStorageKey($element, 'number_of_rows'), count($values));

    // Rebuild the form.
    $form_state->setRebuild();
  }

  /**
   * Form submission handler for adding another condition.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public static function addConditionSubmit(array &$form, FormStateInterface $form_state) {
    // Get the form states element by going up one level.
    $button = $form_state->getTriggeringElement();
    $element =& NestedArray::getValue($form, array_slice($button['#array_parents'], 0, -4));

    // The $row_index is not sequential so we need to rebuild the value instead
    // of just using an array_slice().
    $row_index = $button['#row_index'];
    $values = [];
    foreach ($element['states']['#value'] as $index => $value) {
      $values[] = $value;
      if ($index == $row_index) {
        $values[] = ['selector' => '', 'trigger' => '', 'value' => ''];
      }
    }

    // Reset values.
    $values = array_values($values);

    // Set values.
    $form_state->setValueForElement($element['states'], $values);
    NestedArray::setValue($form_state->getUserInput(), $element['states']['#parents'], $values);

    // Update the number of rows.
    $form_state->set(self::getStorageKey($element, 'number_of_rows'), count($values));

    // Rebuild the form.
    $form_state->setRebuild();
  }

  /**
   * Form submission handler for removing a state or condition.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public static function removeRowSubmit(array &$form, FormStateInterface $form_state) {
    $button = $form_state->getTriggeringElement();
    $element = NestedArray::getValue($form, array_slice($button['#array_parents'], 0, -4));

    $row_index = $button['#row_index'];
    $values = $element['states']['#value'];

    if (isset($values[$row_index]['state'])) {
      // Remove state.
      do {
        unset($values[$row_index]);
        $row_index++;
      } while (isset($values[$row_index]) && !isset($values[$row_index]['state']));
    }
    else {
      // Remove condition.
      unset($values[$row_index]);
    }

    // Reset values.
    $values = array_values($values);

    // Set values.
    $form_state->setValueForElement($element['states'], $values);
    NestedArray::setValue($form_state->getUserInput(), $element['states']['#parents'], $values);

    // Update the number of rows.
    $form_state->set(self::getStorageKey($element, 'number_of_rows'), count($values));

    // Rebuild the form.
    $form_state->setRebuild();
  }

  /**
   * Form submission AJAX callback the returns the states table.
   */
  public static function ajaxCallback(array &$form, FormStateInterface $form_state) {
    $button = $form_state->getTriggeringElement();
    $parent_length = (isset($button['#row_index'])) ? -4 : -1;
    $element = NestedArray::getValue($form, array_slice($button['#array_parents'], 0, $parent_length));
    return $element['states'];
  }

  /**
   * Validates form states element.
   */
  public static function validateStates(&$element, FormStateInterface $form_state, &$complete_form) {
    if (isset($element['states']['#value']) && is_string($element['states']['#value'])) {
      $states = Yaml::decode($element['states']['#value']);
    }
    else {
      $states = self::convertFormValuesToFormApiStates($element['states']['#value']);
    }
    $form_state->setValueForElement($element, NULL);
    $form_state->setValueForElement($element, $states);
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
  protected static function getStorageKey(array $element, $name) {
    return 'yamlform_states__' . $element['#name'] . '__' . $name;
  }

  /****************************************************************************/
  // Convert functions.
  /****************************************************************************/

  /**
   * Convert Form API #states to states array.
   *
   * @param array $fapi_states
   *   An associative array containing Form API #states.
   *
   * @return array
   *   An associative array of states.
   */
  protected static function convertFormApiStatesToStatesArray(array $fapi_states) {
    $index = 0;
    $states = [];
    foreach ($fapi_states as $state => $conditions) {
      $states[$index] = [
        'state' => $state,
        'operator' => 'and',
        'conditions' => [],
      ];

      foreach ($conditions as $condition_key => $condition_value) {
        if (is_string($condition_key)) {
          $states[$index]['conditions'][] = [
            'selector' => $condition_key,
            'trigger' => key($condition_value),
            'value' => reset($condition_value),
          ];
        }
        elseif (is_string($condition_value)) {
          $states[$index]['operator'] = $condition_value;
        }
        else {
          foreach ($condition_value as $subcondition_key => $subcondition_value) {
            $states[$index]['conditions'][] = [
              'selector' => $subcondition_key,
              'trigger' => key($subcondition_value),
              'value' => reset($subcondition_value),
            ];
          }
        }
      }
      $index++;
    }
    return $states;
  }

  /**
   * Convert states array to Form API #states.
   *
   * @param array $states_array
   *   An associative array containing states.
   *
   * @return array
   *   An associative array of states.
   */
  protected static function convertStatesArrayToFormApiStates(array $states_array = []) {
    $states = [];
    foreach ($states_array as $state_array) {
      if ($state = $state_array['state']) {
        $operator = $state_array['operator'];
        $conditions = $state_array['conditions'];
        if (count($conditions) === 1) {
          $condition = reset($conditions);
          $selector = $condition['selector'];
          $trigger = $condition['trigger'];
          if ($selector && $trigger) {
            $value = $condition['value'] ?: TRUE;
          }
          else {
            $value = '';
          }
          $states[$state][$selector][$trigger] = $value;
        }
        else {
          foreach ($state_array['conditions'] as $index => $condition) {
            $selector = $condition['selector'];
            $trigger = $condition['trigger'];
            $value = $condition['value'] ?: TRUE;
            if ($selector && $trigger) {
              if ($index !== 0 && $operator == 'or') {
                $states[$state][] = $operator;
              }
              $states[$state][] = [
                $selector => [
                  $trigger => $value,
                ],
              ];
            }
          }
        }
      }
    }
    return $states;
  }

  /**
   * Convert form values to states array.
   *
   * @param array $values
   *   Submitted form values to converted to states array.
   *
   * @return array
   *   An associative array of states.
   */
  public static function convertFormValuesToStatesArray(array $values = []) {
    $index = 0;

    $states = [];
    foreach ($values as $value) {
      if (isset($value['state'])) {
        $index++;
        $states[$index] = [
          'state' => $value['state'],
          'operator' => $value['operator'],
          'conditions' => [],
        ];
      }
      else {
        $selector = $value['selector']['select'];
        if ($selector == YamlFormSelectOther::OTHER_OPTION) {
          $selector = $value['selector']['other'];
        }
        $value['selector'] = $selector;
        $states[$index]['conditions'][] = $value;
      }
    }
    return $states;
  }

  /**
   * Convert form values to states array.
   *
   * @param array $values
   *   Submitted form values to converted to states array.
   *
   * @return array
   *   An associative array of states.
   */
  public static function convertFormValuesToFormApiStates(array $values = []) {
    $values = self::convertFormValuesToStatesArray($values);
    return self::convertStatesArrayToFormApiStates($values);
  }

  /**
   * Determine if an element's #states array is customized.
   *
   * @param array $element
   *   The form element.
   *
   * @return bool|string
   *   FALSE if #states array is not customized or a warning message.
   */
  public static function isDefaultValueCustomizedFormApiStates(array $element) {
    // Empty default values are not customized.
    if (empty($element['#default_value'])) {
      return FALSE;
    }

    // #states must always be an array.
    if (!is_array($element['#default_value'])) {
      return t('Conditional logic (Form API #states) is not an array.');
    }

    $states = $element['#default_value'];
    foreach ($states as $state => $conditions) {
      if (!isset($element['#state_options'][$state])) {
        return t('Conditional logic (Form API #states) is using a custom %state state.', ['%state' => $state]);
      }

      // If associative array we can assume that it not customized.
      if (YamlFormArrayHelper::isAssociative(($conditions))) {
        $trigger = reset($conditions);
        if (count($trigger) > 1) {
          return t('Conditional logic (Form API #states) is using multiple triggers.');
        }
        continue;
      }

      $operator = NULL;
      foreach ($conditions as $condition) {
        // Make sure only one condition is being specified.
        if (is_array($condition) && count($condition) > 1) {
          return t('Conditional logic (Form API #states) is using multiple nested conditions.');
        }
        elseif (is_string($condition)) {
          // Make sure only an 'and/or' operator is being used. XOR is not
          // support in UI because it is confusing to none technicl users.
          if (!in_array($condition, ['and', 'or'])) {
            return t('Conditional logic (Form API #states) is using the %operator operator.', ['%operator' => Unicode::strtoupper($condition)]);
          }

          // Make sure the same operator is being used between the conditions.
          if ($operator && $operator != $condition) {
            return t('Conditional logic (Form API #states) has multiple operators.', ['%operator' => Unicode::strtoupper($condition)]);
          }

          // Set the operator.
          $operator = $condition;
        }
      }
    }
    return FALSE;
  }

}
