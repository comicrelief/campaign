<?php

/**
 * @file
 * Provides \Drupal\yamlform\YamlFormElementInterface.
 */

namespace Drupal\yamlform;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Defines the interface for YAML form elements.
 *
 * @see \Drupal\yamlform\Annotation\YamlFormElement
 * @see \Drupal\yamlform\YamlFormElementBase
 * @see \Drupal\yamlform\YamlFormElementManager
 * @see plugin_api
 */
interface YamlFormElementInterface extends PluginInspectionInterface {

  /**
   * Checks if YAML form  value could contain multiple lines.
   *
   * @param array $element
   *   An element.
   *
   * @return bool
   *   TRUE is the YAML form element value could contain multiple lines.
   */
  public function isMultiline(array $element);

  /**
   * Prepare an element to be rendered within a form.
   *
   * @param array $element
   *   An element.
   * @param \Drupal\yamlform\YamlFormSubmissionInterface $yamlform_submission
   *   A YAML form submission.
   */
  public function prepare(array &$element, YamlFormSubmissionInterface $yamlform_submission);

  /**
   * Set an element's default value using saved data.
   *
   * @param array $element
   *   An element.
   * @param array|mixed $default_value
   *   The default value which is usually just that the basic data that is sent
   *   to the server then a form element is submitted via a form.
   */
  public function setDefaultValue(array &$element, $default_value);

  /**
   * Save any additional value associated with an element.
   *
   * Currently only applicable to file uploads.
   *
   * @param array $element
   *   An element.
   * @param \Drupal\yamlform\YamlFormSubmissionInterface $yamlform_submission
   *   A YAML form submission.
   */
  public function save(array &$element, YamlFormSubmissionInterface $yamlform_submission);

  /**
   * Delete any additional value associated with an element.
   *
   * Currently only applicable to file uploads.
   *
   * @param array $element
   *   An element.
   * @param \Drupal\yamlform\YamlFormSubmissionInterface $yamlform_submission
   *   A YAML form submission.
   */
  public function postDelete(array &$element, YamlFormSubmissionInterface $yamlform_submission);

  /**
   * Get an element's label (#title or #key).
   *
   * @param array $element
   *   An element.
   *
   * @return string
   *   An element's label (#title or #key).
   */
  public function getLabel(array $element);

  /**
   * Get an element's key/name.
   *
   * @param array $element
   *   An element.
   *
   * @return string
   *   An element's key/name.
   */
  public function getKey(array $element);

  /**
   * Build an element as HTML element.
   *
   * @param array $element
   *   An element.
   * @param array|mixed $value
   *   A value.
   * @param array $options
   *   An array of options.
   *
   * @return array
   *   A render array represent an element as HTML.
   */
  public function buildHtml(array &$element, $value, array $options = []);

  /**
   * Build an element as text element.
   *
   * @param array $element
   *   An element.
   * @param array|mixed $value
   *   A value.
   * @param array $options
   *   An array of options.
   *
   * @return array
   *   A render array represent an element as text.
   */
  public function buildText(array &$element, $value, array $options = []);

  /**
   * Format an element's value as HTML.
   *
   * @param array $element
   *   An element.
   * @param array|mixed $value
   *   A value.
   * @param array $options
   *   An array of options.
   *
   * @return array|string
   *   The element's value formatted as an HTML string or a render array.
   */
  public function formatHtml(array &$element, $value, array $options = []);

  /**
   * Format an element's value as plain text.
   *
   * @param array $element
   *   An element.
   * @param array|mixed $value
   *   A value.
   * @param array $options
   *   An array of options.
   *
   * @return string
   *   The element's value formatted as plain text or a render array.
   */
  public function formatText(array &$element, $value, array $options = []);

  /**
   * Get test value for an element.
   *
   * @param array $element
   *   An element.
   * @param \Drupal\yamlform\YamlFormInterface $yamlform
   *   A YAML form.
   *
   * @return mixed
   *   A test value for an element.
   */
  public function getTestValue(array $element, YamlFormInterface $yamlform);

  /**
   * Get an element's available formats.
   *
   * @return array
   *   An associative array of formats containing name/label pairs.
   */
  public function getFormats();

  /**
   * Get an element's default format name.
   *
   * @return string
   *   An element's default format name.
   */
  public function getDefaultFormat();

  /**
   * Get element's format name by looking for '#format' property, global settings, and finally default settings.
   *
   * @param array $element
   *   An element.
   *
   * @return string
   *   An element's format name.
   */
  public function getFormat(array $element);

  /**
   * Get an element's default export options.
   *
   * @return array
   *   An associative array containing an element's default export options.
   */
  public function getExportDefaultOptions();

  /**
   * Get an element's export options form.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param array $default_values
   *   An associative array of default values.
   *
   * @return array
   *   An associative array contain an element's export option form.
   */
  public function buildExportOptionsForm(array &$form, FormStateInterface $form_state, array $default_values);

  /**
   * Build an element's export header.
   *
   * @param array $element
   *   An element.
   * @param array $options
   *   An associative array of export options.
   *
   * @return array
   *   An array containing the element's export headers.
   *
   * @see \Drupal\yamlform\YamlFormSubmissionExporter::getDefaultExportOptions
   */
  public function buildExportHeader(array $element, array $options);

  /**
   * Build an element's export row.
   *
   * @param array $element
   *   An element.
   * @param array $options
   *   An associative array of export options.
   *
   * @return array
   *   An array containing the element's export row.
   *
   * @see \Drupal\yamlform\YamlFormSubmissionExporter::getDefaultExportOptions
   */
  public function buildExportRecord(array $element, $value, array $options);

}
