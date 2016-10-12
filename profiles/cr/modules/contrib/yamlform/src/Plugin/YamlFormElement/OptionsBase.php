<?php

namespace Drupal\yamlform\Plugin\YamlFormElement;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\OptGroup;
use Drupal\yamlform\Utility\YamlFormArrayHelper;
use Drupal\yamlform\Utility\YamlFormOptionsHelper;
use Drupal\yamlform\YamlFormElementBase;
use Drupal\yamlform\YamlFormSubmissionInterface;

/**
 * Provides a base 'options' element.
 */
abstract class OptionsBase extends YamlFormElementBase {

  /**
   * {@inheritdoc}
   */
  public function getDefaultProperties() {
    return parent::getDefaultProperties() + [
      'options' => [],
      'options_randomize' => FALSE,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getRelatedTypes(array $element) {
    $related_types = parent::getRelatedTypes($element);
    // Remove entity reference elements.
    $elements = $this->elementManager->getInstances();
    foreach ($related_types as $type => $related_type) {
      $element_instance = $elements[$type];
      if ($element_instance instanceof YamlFormEntityReferenceInterface) {
        unset($related_types[$type]);
      }
    }
    return $related_types;
  }

  /**
   * {@inheritdoc}
   */
  public function prepare(array &$element, YamlFormSubmissionInterface $yamlform_submission) {
    parent::prepare($element, $yamlform_submission);

    // Randomize options.
    if (isset($element['#options']) && !empty($element['#options_randomize'])) {
      shuffle($element['#options']);
    }

    $is_wrapper_fieldset = in_array($element['#type'], ['checkboxes', 'radios']);
    if ($is_wrapper_fieldset) {
      // Issue #2396145: Option #description_display for form element fieldset is
      // not changing anything.
      // @see core/modules/system/templates/fieldset.html.twig
      $is_description_display = (isset($element['#description_display'])) ? TRUE : FALSE;
      $has_description = (!empty($element['#description'])) ? TRUE : FALSE;
      if ($is_description_display && $has_description) {
        switch ($element['#description_display']) {
          case 'before':
            $element += ['#field_prefix' => ''];
            $element['#field_prefix'] = '<div class="description">' . $element['#description'] . '</div>' . $element['#field_prefix'];
            unset($element['#description']);
            break;

          case 'invisible':
            $element += ['#field_suffix' => ''];
            $element['#field_suffix'] .= '<div class="description visually-hidden">' . $element['#description'] . '</div>';
            unset($element['#description']);
            break;
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function hasMultipleValues(array $element) {
    return (!empty($element['#multiple'])) ? TRUE : parent::hasMultipleValues($element);
  }

  /**
   * {@inheritdoc}
   */
  public function setDefaultValue(array &$element) {
    if (!isset($element['#default_value'])) {
      return;
    }

    // Compensate for #default_value not being an array, for elements that
    // allow for multiple #options to be selected/checked.
    if ($this->hasMultipleValues($element) && !is_array($element['#default_value'])) {
      $element['#default_value'] = [$element['#default_value']];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function formatHtml(array &$element, $value, array $options = []) {
    if ($value && is_array($value) && ($list_type = $this->getListType($element))) {
      $flattened_options = OptGroup::flattenOptions($element['#options']);
      return [
        '#theme' => 'item_list',
        '#items' => YamlFormOptionsHelper::getOptionsText($value, $flattened_options),
        '#list_type' => $list_type,
      ];
    }
    else {
      return parent::formatHtml($element, $value, $options);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function formatText(array &$element, $value, array $options = []) {
    // Return empty value.
    if ($value === '' || $value === NULL || (is_array($value) && empty($value))) {
      return '';
    }

    $format = $this->getFormat($element);
    $flattened_options = OptGroup::flattenOptions($element['#options']);

    // If not multiple options array return the simple value.
    if (!is_array($value)) {
      return ($format == 'raw') ? $value : YamlFormOptionsHelper::getOptionText($value, $flattened_options);
    }

    $options_text = YamlFormOptionsHelper::getOptionsText($value, $flattened_options);
    switch ($format) {
      case 'ol';
        $list = [];
        $index = 1;
        foreach ($options_text as $option_text) {
          $list[] = ($index++) . '. ' . $option_text;
        }
        return implode("\n", $list);

      case 'ul';
        $list = [];
        foreach ($options_text as $index => $option_text) {
          $list[] = '- ' . $option_text;
        }
        return implode("\n", $list);

      case 'and':
        return YamlFormArrayHelper::toString($options_text, t('and'));

      case 'comma';
        return implode(', ', $options_text);

      case 'semicolon';
        return implode('; ', $options_text);

      case 'raw';
        return implode(', ', $value);

      default:
        return implode($format, $options_text);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function isMultiline(array $element) {
    if ($this->isList($element)) {
      return TRUE;
    }
    else {
      return parent::isMultiline($element);
    }
  }

  /**
   * Determine if options element is being displayed as list.
   *
   * @param array $element
   *   An element.
   *
   * @return bool
   *   TRUE if options element is being displayed as ul or ol list.
   */
  protected function isList(array $element) {
    return ($this->getListType($element)) ? TRUE : FALSE;
  }

  /**
   * Get the element's list type.
   *
   * @param array $element
   *   An element.
   *
   * @return string
   *   List type ul or ol or NULL is element is not formatted as a list.
   */
  protected function getListType(array $element) {
    $format = $this->getFormat($element);
    switch ($format) {
      case 'ol':
      case 'ordered':
        return 'ol';

      case 'ul':
      case 'unordered':
      case 'list':
        return 'ul';

      default:
        return NULL;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultFormat() {
    return 'comma';
  }

  /**
   * {@inheritdoc}
   */
  public function getFormats() {
    return parent::getFormats() + [
      'comma' => $this->t('Comma'),
      'semicolon' => $this->t('Semicolon'),
      'and' => $this->t('And'),
      'ol' => $this->t('Ordered list'),
      'ul' => $this->t('Unordered list'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getTableColumn(array $element) {
    $key = $element['#yamlform_key'];
    $columns = parent::getTableColumn($element);
    $columns['element__' . $key]['sort'] = !$this->hasMultipleValues($element);
    return $columns;
  }

  /**
   * {@inheritdoc}
   */
  public function getExportDefaultOptions() {
    return [
      'options_format' => 'compact',
      'options_item_format' => 'label',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildExportOptionsForm(array &$form, FormStateInterface $form_state, array $default_values) {
    if (isset($form['options'])) {
      return;
    }

    $form['options'] = [
      '#type' => 'details',
      '#title' => $this->t('Select menu, radio buttons, and checkboxes options'),
      '#open' => TRUE,
      '#weight' => -10,
    ];
    $form['options']['options_format'] = [
      '#type' => 'radios',
      '#title' => $this->t('Options format'),
      '#options' => [
        'compact' => $this->t('Compact; with the option values delimited by commas in one column.') . '<div class="description">' . $this->t('Compact options are more suitable for importing data into other systems.') . '</div>',
        'separate' => $this->t('Separate; with each possible option value in its own column.') . '<div class="description">' . $this->t('Separate options are more suitable for building reports, graphs, and statistics in a spreadsheet application.') . '</div>',
      ],
      '#default_value' => $default_values['options_format'],
    ];
    $form['options']['options_item_format'] = [
      '#type' => 'radios',
      '#title' => $this->t('Options item format'),
      '#options' => [
        'label' => $this->t('Option labels, the human-readable value (label)'),
        'key' => $this->t('Option values, the raw value stored in the database (key)'),
      ],
      '#default_value' => $default_values['options_item_format'],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildExportHeader(array $element, array $options) {
    if ($options['options_format'] == 'separate' && isset($element['#options'])) {
      $header = [];
      foreach ($element['#options'] as $option_value => $option_text) {
        $header[] = ($options['options_item_format'] == 'key') ? $option_value : $option_text;
      }
      return $this->prefixExportHeader($header, $element, $options);
    }
    else {
      return parent::buildExportHeader($element, $options);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function buildExportRecord(array $element, $value, array $options) {
    $element_options = $element['#options'];

    $record = [];
    if ($options['options_format'] == 'separate') {
      // Combine the values so that isset can be used instead of in_array().
      // http://stackoverflow.com/questions/13483219/what-is-faster-in-array-or-isset
      if (is_array($value)) {
        $value = array_combine($value, $value);
      }
      // Separate multiple values (ie options).
      foreach ($element_options as $option_value => $option_text) {
        if (is_array($value) && isset($value[$option_value])) {
          $record[] = 'X';
        }
        elseif ($value == $option_value) {
          $record[] = 'X';
        }
        else {
          $record[] = '';
        }
      }
    }
    else {
      // Handle multiple values with options.
      if (is_array($value)) {
        if ($options['options_item_format'] == 'label') {
          $value = YamlFormOptionsHelper::getOptionsText($value, $element_options);
        }
        $record[] = implode(',', $value);
      }
      // Handle single values with options.
      elseif ($options['options_item_format'] == 'label') {
        $record[] = YamlFormOptionsHelper::getOptionText($value, $element_options);
      }
    }

    return $record;
  }

  /**
   * Form API callback. Remove unchecked options from value array.
   */
  public static function validateMultipleOptions(array &$element, FormStateInterface $form_state) {
    $name = $element['#name'];
    $values = $form_state->getValue($name);
    $values = array_filter($values);
    $values = array_values($values);
    $form_state->setValue($name, $values);
  }

  /**
   * {@inheritdoc}
   */
  protected function getElementSelectorInputsOptions(array $element) {
    $plugin_id = $this->getPluginId();
    if (preg_match('/yamlform_(select|radios|checkboxes)_other$/', $plugin_id, $match)) {
      $title = $this->getAdminLabel($element);
      list($element_type) = explode(' ', $this->getPluginLabel());

      $inputs = [];
      $inputs[$match[1]] = $title . ' [' . $element_type . ']';
      $inputs['other'] = $title . ' [' . $this->t('Text field') . ']';
      return $inputs;
    }
    else {
      return [];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $form['general']['default_value']['#description'] = $this->t('The default value of the field identified by its key.');
    $form['general']['default_value']['#description'] .= ' ' . $this->t('For multiple options use commas to separate multiple defaults.');

    $form['options'] = [
      '#type' => 'details',
      '#title' => $this->t('Options'),
      '#open' => TRUE,
    ];
    $form['options']['options'] = [
      '#type' => 'yamlform_element_options',
      '#title' => $this->t('Options'),
      '#required' => TRUE,
    ];
    $form['options']['options_display'] = [
      '#title' => $this->t('Options display'),
      '#type' => 'select',
      '#options' => [
        'one_column' => $this->t('One column'),
        'two_columns' => $this->t('Two columns'),
        'three_columns' => $this->t('Three columns'),
        'side_by_side' => $this->t('Side by side'),
      ],
    ];
    $form['options']['empty_option'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Empty option label'),
      '#description' => $this->t('The label to show for the initial option denoting no selection in a select element.'),
    ];
    $form['options']['empty_value'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Empty option value'),
      '#description' => $this->t('The value for the initial option denoting no selection in a select element, which is used to determine whether the user submitted a value or not.'),
    ];
    $form['options']['options_randomize'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Randomize options'),
      '#description' => $this->t('Randomizes the order of the options when they are displayed in the form.'),
      '#return_value' => TRUE,
    ];

    $form['options_other'] = [
      '#type' => 'details',
      '#title' => $this->t('Other option'),
      '#open' => TRUE,
    ];
    $form['options_other']['other__option_label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Other option label'),
    ];
    $form['options_other']['other__title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Other title'),
    ];
    $form['options_other']['other__placeholder'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Other placeholder'),
    ];
    $form['options_other']['other__description'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Other description'),
    ];
    $form['options_other']['other__size'] = [
      '#type' => 'number',
      '#title' => $this->t('Other size'),
      '#description' => $this->t('Leaving blank will use the default size.'),
      '#min' => 1,
      '#size' => 4,
    ];
    $form['options_other']['other__maxlength'] = [
      '#type' => 'number',
      '#title' => $this->t('Other maxlength'),
      '#description' => $this->t('Leaving blank will use the default maxlength.'),
      '#min' => 1,
      '#size' => 4,
    ];

    return $form;
  }

}
