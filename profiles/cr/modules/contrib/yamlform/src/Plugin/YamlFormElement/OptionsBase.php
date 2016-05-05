<?php

/**
 * @file
 * Contains \Drupal\yamlform\Plugin\YamlFormElement\OptionsBase.
 */

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
  public function prepare(array &$element, YamlFormSubmissionInterface $yamlform_submission) {
    $element['#element_validate'][] = [get_class($this), 'validate'];
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

    $flattened_options = OptGroup::flattenOptions($element['#options']);

    // If not multiple options array return the simple value.
    if (!is_array($value)) {
      return YamlFormOptionsHelper::getOptionText($value, $flattened_options);
    }

    $format = $this->getFormat($element);
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
        return implode(', ', YamlFormOptionsHelper::getOptionsText($value, $flattened_options));

      case 'semicolon';
        return implode('; ', YamlFormOptionsHelper::getOptionsText($value, $flattened_options));

      default:
        return implode($format, YamlFormOptionsHelper::getOptionsText($value, $flattened_options));
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
      return $header;
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
  public static function validate(array &$element, FormStateInterface $form_state) {
    $name = $element['#name'];
    $values = $form_state->getValue($name);
    $values = array_filter($values);
    $form_state->setValue($name, $values);
  }

}
