<?php

namespace Drupal\yamlform;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\PluginFormInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;

/**
 * Defines the interface for form elements.
 *
 * @see \Drupal\yamlform\Annotation\YamlFormElement
 * @see \Drupal\yamlform\YamlFormElementBase
 * @see \Drupal\yamlform\YamlFormElementManager
 * @see \Drupal\yamlform\YamlFormElementManagerInterface
 * @see plugin_api
 */
interface YamlFormElementInterface extends PluginInspectionInterface, PluginFormInterface, ContainerFactoryPluginInterface {

  /**
   * Get the URL for the element's API documentation.
   *
   * @return \Drupal\Core\Url|null
   *   The the URL for the element's API documentation.
   */
  public function getPluginApiUrl();

  /**
   * Get link to element's API documentation.
   *
   * @return \Drupal\Core\GeneratedLink|string
   *   A link to element's API documentation.
   */
  public function getPluginApiLink();

  /**
   * Gets the label of the plugin instance.
   *
   * @return string
   *   The label of the plugin instance.
   */
  public function getPluginLabel();

  /**
   * Get default properties.
   *
   * @return array
   *   An associative array containing default element properties.
   */
  public function getDefaultProperties();

  /**
   * Determine if an element supports a specified property.
   *
   * @param string $property_name
   *   An element's property name.
   *
   * @return bool
   *   TRUE if the element supports a specified property.
   */
  public function hasProperty($property_name);

  /**
   * Checks if the form element carries a value.
   *
   * @param array $element
   *   An element.
   *
   * @return bool
   *   TRUE if the form element carries a value.
   */
  public function isInput(array $element);

  /**
   * Checks if the form element has a wrapper.
   *
   * @param array $element
   *   An element.
   *
   * @return bool
   *   TRUE if the form element has a wrapper.
   */
  public function hasWrapper(array $element);

  /**
   * Checks if form element is a container that can contain elements.
   *
   * @param array $element
   *   An element.
   *
   * @return bool
   *   TRUE if the form element is a container that can contain elements.
   */
  public function isContainer(array $element);

  /**
   * Checks if form element is a root element.
   *
   * @param array $element
   *   An element.
   *
   * @return bool
   *   TRUE if the form element is a root element.
   */
  public function isRoot(array $element);

  /**
   * Checks if form element value could contain multiple lines.
   *
   * @param array $element
   *   An element.
   *
   * @return bool
   *   TRUE if the form element value could contain multiple lines.
   */
  public function isMultiline(array $element);

  /**
   * Checks if form element is a composite element.
   *
   * @param array $element
   *   An element.
   *
   * @return bool
   *   TRUE if the form element is a composite element.
   */
  public function isComposite(array $element);

  /**
   * Checks if form element is hidden.
   *
   * @param array $element
   *   An element.
   *
   * @return bool
   *   TRUE if the form element is hidden.
   */
  public function isHidden(array $element);

  /**
   * Checks if form element value has multiple values.
   *
   * @param array $element
   *   An element.
   *
   * @return bool
   *   TRUE if form element value has multiple values.
   */
  public function hasMultipleValues(array $element);

  /**
   * Retrieves the default properties for the defined element type.
   *
   * @return array
   *   An associative array describing the element types being defined.
   *
   * @see \Drupal\Core\Render\ElementInfoManagerInterface::getInfo
   */
  public function getInfo();

  /**
   * Get related element types.
   *
   * @param array $element
   *   The element.
   *
   * @return array
   *   An array containing related element types.
   */
  public function getRelatedTypes(array $element);

  /**
   * Gets the actual configuration form array to be built.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   An associative array contain the element's configuration form without
   *   any default values..
   */
  public function form(array $form, FormStateInterface $form_state);

  /**
   * Initialize an element to be displayed, rendered, or exported.
   *
   * @param array $element
   *   An element.
   */
  public function initialize(array &$element);

  /**
   * Prepare an element to be rendered within a form.
   *
   * @param array $element
   *   An element.
   * @param \Drupal\yamlform\YamlFormSubmissionInterface $yamlform_submission
   *   A form submission.
   */
  public function prepare(array &$element, YamlFormSubmissionInterface $yamlform_submission);

  /**
   * Set an element's default value using saved data.
   *
   * @param array $element
   *   An element.
   */
  public function setDefaultValue(array &$element);

  /**
   * Get an element's label (#title or #yamlform_key).
   *
   * @param array $element
   *   An element.
   *
   * @return string
   *   An element's label (#title or #yamlform_key).
   */
  public function getLabel(array $element);

  /**
   * Get an element's admin label (#admin_title, #title or #yamlform_key).
   *
   * @param array $element
   *   An element.
   *
   * @return string
   *   An element's label (#admin_title, #title or #yamlform_key).
   */
  public function getAdminLabel(array $element);

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
   *   A render array representing an element as HTML.
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
   *   A render array representing an element as text.
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
   *   A form.
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
   * Get element's table column(s) settings.
   *
   * @param array $element
   *   An element.
   *
   * @return array
   *   An associative array containing an element's table column(s).
   */
  public function getTableColumn(array $element);

  /**
   * Format an element's table column value.
   *
   * @param array $element
   *   An element.
   * @param array|mixed $value
   *   A value.
   * @param array $options
   *   An array of options returned from ::getTableColumns().
   *
   * @return array|string
   *   The element's value formatted as an HTML string or a render array.
   */
  public function formatTableColumn(array $element, $value, array $options = []);

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
   * Get an associative array of element properties from configuration form.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   An associative array of element properties.
   */
  public function getConfigurationFormProperties(array &$form, FormStateInterface $form_state);

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
   * @see \Drupal\yamlform\YamlFormSubmissionExporterInterface::getDefaultExportOptions
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
   * @see \Drupal\yamlform\YamlFormSubmissionExporterInterface::getDefaultExportOptions
   */
  public function buildExportRecord(array $element, $value, array $options);

  /**
   * Get an element's supported states as options.
   *
   * @return array
   *   An array of element states.
   */
  public function getElementStateOptions();

  /**
   * Get an element's selectors as options.
   *
   * @param array $element
   *   An element.
   *
   * @return array
   *   An array of element selectors.
   */
  public function getElementSelectorOptions(array $element);

  /**
   * Changes the values of an entity before it is created.
   *
   * @param array $element
   *   An element.
   * @param mixed[] $values
   *   An array of values to set, keyed by property name.
   */
  public function preCreate(array &$element, array $values);

  /**
   * Acts on a form submission element after it is created.
   *
   * @param array $element
   *   An element.
   * @param \Drupal\yamlform\YamlFormSubmissionInterface $yamlform_submission
   *   A form submission.
   */
  public function postCreate(array &$element, YamlFormSubmissionInterface $yamlform_submission);

  /**
   * Acts on loaded form submission.
   *
   * @param array $element
   *   An element.
   * @param \Drupal\yamlform\YamlFormSubmissionInterface $yamlform_submission
   *   A form submission.
   */
  public function postLoad(array &$element, YamlFormSubmissionInterface $yamlform_submission);

  /**
   * Acts on a form submission element before the presave hook is invoked.
   *
   * @param array $element
   *   An element.
   * @param \Drupal\yamlform\YamlFormSubmissionInterface $yamlform_submission
   *   A form submission.
   */
  public function preSave(array &$element, YamlFormSubmissionInterface $yamlform_submission);

  /**
   * Acts on a saved form submission element before the insert or update hook is invoked.
   *
   * @param array $element
   *   An element.
   * @param \Drupal\yamlform\YamlFormSubmissionInterface $yamlform_submission
   *   A form submission.
   * @param bool $update
   *   TRUE if the entity has been updated, or FALSE if it has been inserted.
   */
  public function postSave(array &$element, YamlFormSubmissionInterface $yamlform_submission, $update = TRUE);

  /**
   * Delete any additional value associated with an element.
   *
   * Currently only applicable to file uploads.
   *
   * @param array $element
   *   An element.
   * @param \Drupal\yamlform\YamlFormSubmissionInterface $yamlform_submission
   *   A form submission.
   */
  public function postDelete(array &$element, YamlFormSubmissionInterface $yamlform_submission);

}
