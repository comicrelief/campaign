<?php

/**
 * @file
 * Provides \Drupal\yamlform\YamlFormElementBase.
 */

namespace Drupal\yamlform;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Component\Utility\Xss;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Provides a base class for a YAML form element.
 *
 * @see \Drupal\yamlform\YamlFormElementInterface
 * @see \Drupal\yamlform\YamlFormElementManager
 * @see plugin_api
 */
class YamlFormElementBase extends PluginBase implements YamlFormElementInterface {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function isMultiline(array $element) {
    return $this->pluginDefinition['multiline'];
  }

  /**
   * {@inheritdoc}
   */
  public function prepare(array &$element, YamlFormSubmissionInterface $yamlform_submission) {}

  /**
   * {@inheritdoc}
   */
  public function setDefaultValue(array &$element, $default_value) {}

  /**
   * {@inheritdoc}
   */
  public function save(array &$element, YamlFormSubmissionInterface $yamlform_submission) {}

  /**
   * {@inheritdoc}
   */
  public function postDelete(array &$element, YamlFormSubmissionInterface $yamlform_submission) {}

  /**
   * {@inheritdoc}
   */
  public function getLabel(array $element) {
    return (!empty($element['#title'])) ? $element['#title'] : $element['#key'];
  }

  /**
   * {@inheritdoc}
   */
  public function getKey(array $element) {
    return $element['#key'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildHtml(array &$element, $value, array $options = []) {
    return $this->build('html', $element, $value, $options);
  }

  /**
   * {@inheritdoc}
   */
  public function buildText(array &$element, $value, array $options = []) {
    return $this->build('text', $element, $value, $options);
  }

  /**
   * Build an element as text or HTML.
   *
   * @param string $format
   *   Format of the element, text or html.
   * @param array $element
   *   An element.
   * @param array|mixed $value
   *   A value.
   * @param array $options
   *   An array of options.
   *
   * @return array
   *   A render array represent an element as text or HTML.
   */
  protected function build($format, array &$element, $value, array $options = []) {
    $options['multiline'] = $this->isMultiline($element);

    $format_function = 'format' . ucfirst($format);
    $formatted_value = $this->$format_function($element, $value, $options);
    // Convert string to renderable #markup.
    if (is_string($formatted_value)) {
      $formatted_value = ['#markup' => $formatted_value];
    }

    return [
      '#theme' => 'yamlform_element_base_' . $format,
      '#element' => $element,
      '#value' => $formatted_value,
      '#options' => $options,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function formatHtml(array &$element, $value, array $options = []) {
    return $this->formatText($element, $value, $options);
  }

  /**
   * {@inheritdoc}
   */
  public function formatText(array &$element, $value, array $options = []) {
    // Return empty value.
    if ($value === '' || $value === NULL) {
      return '';
    }

    // Apply XSS filter to value that contains HTML tags and is not formatted as
    // raw.
    $format = $this->getFormat($element);
    if ($format != 'raw' && is_string($value) && strpos($value, '<') !== FALSE) {
      $value = Xss::filter($value);
    }

    // Format value based on the element type using default settings.
    if (isset($element['#type'])) {
      // Apply #field prefix and #field_suffix to value.
      if (isset($element['#field_prefix'])) {
        $value = $element['#field_prefix'] . $value;
      }
      if (isset($element['#field_suffix'])) {
        $value .= $element['#field_suffix'];
      }
    }

    return $value;
  }

  /**
   * {@inheritdoc}
   */
  public function getTestValue(array $element, YamlFormInterface $yamlform) {
    return '';
  }

  /**
   * {@inheritdoc}
   */
  public function getFormats() {
    return [
      'value' => $this->t('Value'),
      'raw' => $this->t('Raw value'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultFormat() {
    return 'value';
  }

  /**
   * {@inheritdoc}
   */
  public function getFormat(array $element) {
    if (isset($element['#format'])) {
      return $element['#format'];
    }
    elseif ($default_format = \Drupal::config('yamlform.settings')->get('format.' . $this->getPluginId())) {
      return $default_format;
    }
    else {
      return $this->getDefaultFormat();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getExportDefaultOptions() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function buildExportOptionsForm(array &$form, FormStateInterface $form_state, array $default_values) {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function buildExportHeader(array $element, array $options) {
    if ($options['header_keys'] == 'label') {
      return [$this->getLabel($element)];
    }
    else {
      return [$element['#key']];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function buildExportRecord(array $element, $value, array $options) {
    $element['#format'] = 'raw';
    return [$this->formatText($element, $value, $options)];
  }

}
