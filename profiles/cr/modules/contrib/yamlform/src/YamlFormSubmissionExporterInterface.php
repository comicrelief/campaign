<?php

namespace Drupal\yamlform;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Defines an interface for exporting form submission results.
 */
interface YamlFormSubmissionExporterInterface {

  /**
   * Set the form whose submissions are being exported.
   *
   * @param \Drupal\yamlform\YamlFormInterface $yamlform
   *   A form.
   */
  public function setYamlForm(YamlFormInterface $yamlform = NULL);

  /**
   * Get the form whose submissions are being exported.
   *
   * @return \Drupal\yamlform\YamlFormInterface
   *   A form.
   */
  public function getYamlForm();

  /**
   * Set the form source entity whose submissions are being exported.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   A form's source entity.
   */
  public function setSourceEntity(EntityInterface $entity = NULL);

  /**
   * Get the form source entity whose submissions are being exported.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   A form's source entity.
   */
  public function getSourceEntity();

  /**
   * Get export options for the current form and entity.
   *
   * @return array
   *   Export options.
   */
  public function getYamlFormOptions();

  /**
   * Set export options for the current form and entity.
   *
   * @param array $options
   *   Export options.
   */
  public function setYamlFormOptions(array $options = []);

  /**
   * Delete export options for the current form and entity.
   */
  public function deleteYamlFormOptions();

  /**
   * Get default options for exporting a CSV.
   *
   * @return array
   *   Default options for exporting a CSV.
   */
  public function getDefaultExportOptions();

  /**
   * {@inheritdoc}
   */
  public function buildForm(array &$form, FormStateInterface $form_state);

  /**
   * Get the values from the form's state.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   An associative array of export options.
   */
  public function getFormValues(FormStateInterface $form_state);

  /**
   * Generate form submission as a CSV and write it to a temp file.
   *
   * @param array $export_options
   *   An associative array of export options generated via the
   *   Drupal\yamlform\Form\YamlFormResultsExportForm.
   */
  public function generate(array $export_options);

  /**
   * Write form results header to CSV file.
   *
   * @param array $field_definitions
   *   An associative array containing form submission field definitions.
   * @param array $elements
   *   An associative array containing form elements.
   * @param array $export_options
   *   An associative array of export options.
   */
  public function writeHeader(array $field_definitions, array $elements, array $export_options);

  /**
   * Write form results header to CSV file.
   *
   * @param \Drupal\yamlform\YamlFormSubmissionInterface[] $yamlform_submissions
   *   A form submission.
   * @param array $field_definitions
   *   An associative array containing form submission field definitions.
   * @param array $elements
   *   An associative array containing form elements.
   * @param array $export_options
   *   An associative array of export options.
   */
  public function writeRecords(array $yamlform_submissions, array $field_definitions, array $elements, array $export_options);

  /**
   * Write CSV file to Archive file.
   *
   * @param array $export_options
   *   An associative array of export options.
   */
  public function writeCsvToArchive(array $export_options);

  /**
   * Get form submission field definitions.
   *
   * @param array $export_options
   *   An associative array of export options.
   *
   * @return array
   *   An associative array containing form submission field definitions.
   */
  public function getFieldDefinitions(array $export_options);

  /**
   * Get form elements.
   *
   * @param array $export_options
   *   An associative array of export options.
   *
   * @return array
   *   An associative array containing form elements keyed by name.
   */
  public function getElements(array $export_options);

  /**
   * Get form submission query for specified YAMl form and export options.
   *
   * @param array $export_options
   *   An associative array of export options.
   *
   * @return \Drupal\Core\Entity\Query\QueryInterface
   *   A form submission entity query.
   */
  public function getQuery(array $export_options);

  /**
   * Total number of submissions to be exported.
   *
   * @param array $export_options
   *   An associative array of export options.
   *
   * @return int
   *   The total number of submissions to be exported.
   */
  public function getTotal(array $export_options);

  /**
   * Get the number of submissions to be exported with each batch.
   *
   * @return int
   *   Number of submissions to be exported with each batch.
   */
  public function getBatchLimit();

  /**
   * Determine if form submissions must be exported using batch processing.
   *
   * @return bool
   *   TRUE if form submissions must be exported using batch processing.
   */
  public function requiresBatch();

  /**
   * Get CSV file temp directory path.
   *
   * @return string
   *   Temp directory path.
   */
  public function getFileTempDirectory();

  /**
   * Get CSV file name and path for a form.
   *
   * @param array $export_options
   *   An associative array of export options.
   *
   * @return string
   *   CSV file name and path for a form
   */
  public function getCsvFilePath(array $export_options);

  /**
   * Get CSV file name for a form.
   *
   * @param array $export_options
   *   An associative array of export options.
   *
   * @return string
   *   CSV or TSV file name for a form depending on the delimiter.
   */
  public function getCsvFileName(array $export_options);

  /**
   * Get archive file name and path for a form.
   *
   * @param array $export_options
   *   An associative array of export options.
   *
   * @return string
   *   Archive file name and path for a form
   */
  public function getArchiveFilePath(array $export_options);

  /**
   * Get archive file name for a form.
   *
   * @param array $export_options
   *   An associative array of export options.
   *
   * @return string
   *   Archive file name.
   */
  public function getArchiveFileName(array $export_options);

  /**
   * Determine if an archive is being generated.
   *
   * @param array $export_options
   *   An associative array of export options.
   *
   * @return bool
   *   TRUE if an archive is being generated.
   */
  public function isArchive(array $export_options);

  /**
   * Determine if export needs to use batch processing.
   *
   * @param array $export_options
   *   An associative array of export options generated via the
   *   Drupal\yamlform\Form\YamlFormResultsExportForm.
   *
   * @return bool
   *   TRUE if export needs to use batch processing.
   */
  public function isBatch(array $export_options);

}
