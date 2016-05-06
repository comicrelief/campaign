<?php

/**
 * @file
 * Contains \Drupal\yamlform\YamlFormSubmissionExporter.
 */

namespace Drupal\yamlform;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\yamlform\Entity\YamlFormSubmission;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Exporter for YAML form submission results.
 */
class YamlFormSubmissionExporter {

  use StringTranslationTrait;

  /**
   * The configuration object factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * YAML form submission storage.
   *
   * @var \Drupal\yamlform\YamlFormSubmissionStorageInterface
   */
  protected $entityStorage;

  /**
   * The entity query factory.
   *
   * @var \Drupal\Core\Entity\Query\QueryFactory
   */
  protected $queryFactory;

  /**
   * YAML form element manager.
   *
   * @var \Drupal\yamlform\YamlFormElementManager
   */
  protected $yamlformElementManager;


  /**
   * Default export options.
   *
   * @var array.
   */
  protected $defaultOptions;

  /**
   * Constructs a YamlFormSubmissionExporter object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The configuration object factory.
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager.
   * @param \Drupal\Core\Entity\Query\QueryFactory $query_factory
   *   The entity query factory.
   * @param \Drupal\yamlform\YamlFormElementManager $yamlform_element_manager
   *   The YAML form element manager.
   */
  public function __construct(ConfigFactoryInterface $config_factory, EntityManagerInterface $entity_manager, QueryFactory $query_factory, YamlFormElementManager $yamlform_element_manager) {
    $this->configFactory = $config_factory;
    $this->entityStorage = $entity_manager->getStorage('yamlform_submission')->getFieldDefinitions();
    $this->queryFactory = $query_factory;
    $this->yamlformElementManager = $yamlform_element_manager;
  }

  /****************************************************************************/
  // Default options and form.
  /****************************************************************************/

  /**
   * Get default options for exporting a CSV.
   *
   * @return array
   *   Default options for exporting a CSV.
   */
  public function getDefaultExportOptions() {
    if (isset($this->defaultOptions)) {
      return $this->defaultOptions;
    }

    $this->defaultOptions = [
      'delimiter' => ',',
      'header_keys' => 'label',
      'excluded_columns' => [
        'uuid' => 'uuid',
        'token' => 'token',
        'changed' => 'changed',
        'data' => 'data',
        'yamlform_id' => 'yamlform_id',
      ],
      'range_type' => 'all',
      'range_latest' => '',
      'range_start' => '',
      'range_end' => '',
      'state' => 'all',
      'download' => TRUE,
    ];

    // Append element handler default options.
    $element_handlers = $this->yamlformElementManager->getInstances();
    foreach ($element_handlers as $element_handler) {
      $this->defaultOptions += $element_handler->getExportDefaultOptions();
    }

    return $this->defaultOptions;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array &$form, FormStateInterface $form_state, YamlFormInterface $yamlform = NULL) {
    $default_options = $this->getDefaultExportOptions();
    $values = $form_state->getValues();
    $default_values = NestedArray::mergeDeep($default_options, $values);

    $form['export']['#tree'] = TRUE;

    $form['export']['format'] = [
      '#type' => 'details',
      '#title' => $this->t('Format options'),
      '#open' => TRUE,
    ];
    $form['export']['format']['delimiter'] = [
      '#type' => 'select',
      '#title' => $this->t('Delimiter text format'),
      '#description' => $this->t('This is the delimiter used in the CSV/TSV file when downloading YAML form results. Using tabs in the export is the most reliable method for preserving non-latin characters. You may want to change this to another character depending on the program with which you anticipate importing results.'),
      '#options' => [
        ','  => $this->t('Comma (,)'),
        '\t' => $this->t('Tab (\t)'),
        ';'  => $this->t('Semicolon (;)'),
        ':'  => $this->t('Colon (:)'),
        '|'  => $this->t('Pipe (|)'),
        '.'  => $this->t('Period (.)'),
        ' '  => $this->t('Space ( )'),
      ],
      '#default_value' => $default_values['delimiter'],
    ];

    $form['export']['format']['header_keys'] = [
      '#type' => 'radios',
      '#title' => $this->t('Column header format'),
      '#description' => $this->t('Choose whether to show the label or field key in each column header.'),
      '#options' => [
        'label' => $this->t('Input titles (label)'),
        'key' => $this->t('Input names (key)'),
      ],
      '#default_value' => $default_values['header_keys'],
    ];

    // Build element specific export forms.
    // Grouping everything in $form['export']['elements'] so that element handlers can
    // assign #weight to its export options form.
    $form['export']['elements'] = [];
    $element_handlers = $this->yamlformElementManager->getInstances();
    foreach ($element_handlers as $element_handler) {
      $element_handler->buildExportOptionsForm($form['export']['elements'], $form_state, $default_values);
    }

    // All the remain options are only applicable to a YAML form's export.
    // @see Drupal\yamlform\Form\YamlFormResultsExportForm
    if (!$yamlform) {
      return;
    }

    // Inputs.
    $form['export']['inputs'] = [
      '#type' => 'details',
      '#title' => t('Column options'),
    ];
    $form['export']['inputs']['excluded_columns'] = [
      '#type' => 'yamlform_excluded_columns',
      '#description' => $this->t('The selected columns will be included in the export.'),
      '#yamlform' => $yamlform,
      '#default_value' => $default_values['excluded_columns'],
    ];

    // Download options.
    $form['export']['download'] = [
      '#type' => 'details',
      '#title' => $this->t('Download options'),
      '#open' => TRUE,
    ];
    $form['export']['download']['download'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Download CSV'),
      '#description' => $this->t('If checked the CSV file will be automatically download to your local machine. If unchecked CSV file will be displayed as plain text within your browser.'),
      '#default_value' => $default_values['download'],
      '#access' => !$this->requiresBatch($yamlform),
    ];
    $form['export']['download']['range_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Limit download to'),
      '#options' => [
        'all' => t('All (@total)', ['@total' => $this->getTotal($yamlform, $this->getDefaultExportOptions())]),
        'latest' => t('Latest'),
        'sid' => t('Submission ID'),
        'date' => t('Date'),
      ],
      '#default_value' => $default_values['range_type'],
    ];
    $form['export']['download']['latest'] = [
      '#type' => 'container',
      '#attributes' => ['class' => 'container-inline'],
      '#states' => [
        'visible' => [
          ':input[name="export[download][range_type]"]' => ['value' => 'latest'],
        ],
      ],
      'range_latest' => [
        '#type' => 'number',
        '#title' => $this->t('Number of submissions'),
        '#default_value' => $default_values['range_latest'],
      ],
    ];
    $ranges = [
      'sid' => ['#type' => 'number'],
      'date' => ['#type' => 'date'],
    ];
    foreach ($ranges as $key => $range_element) {
      $form['export']['download'][$key] = [
        '#type' => 'container',
        '#attributes' => ['class' => 'container-inline'],
        '#states' => [
          'visible' => [
            ':input[name="export[download][range_type]"]' => ['value' => $key],
          ],
        ],
      ];
      $form['export']['download'][$key]['range_start'] = $range_element + [
        '#title' => $this->t('From'),
        '#default_value' => $default_values['range_start'],
      ];
      $form['export']['download'][$key]['range_end'] = $range_element + [
        '#title' => $this->t('To'),
        '#default_value' => $default_values['range_end'],
      ];
    }

    // If drafts are allowed, provide options to filter download based on
    // submission state.
    $form['export']['download']['state'] = [
      '#type' => 'radios',
      '#title' => t('Submission state'),
      '#default_value' => $default_values['state'],
      '#options' => [
        'all' => t('Completed and draft submissions'),
        'completed' => t('Completed submissions only'),
        'draft' => t('Drafts only'),
      ],
      '#access' => $yamlform->getSetting('draft'),
    ];
  }

  /**
   * Get the values from the form's state.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   An associative array of export options.
   */
  public function getFormValues(FormStateInterface $form_state) {
    $export_values = $form_state->getValue('export');
    $values = [];

    // Append download/range type.
    if (isset($export_values['download'])) {
      if (isset($export_values['download']['range_type'])) {
        $range_type = $export_values['download']['range_type'];
        $values['range_type'] = $range_type;
        if (isset($export_values['download'][$range_type])) {
          $values += $export_values['download'][$range_type];
        }
      }
      $values['state'] = $export_values['download']['state'];
      $values['download'] = $export_values['download']['download'];
    }

    // Append format and inputs.
    if (isset($export_values['format'])) {
      $values += $export_values['format'];
    }
    if (isset($export_values['inputs'])) {
      $values += $export_values['inputs'];
    }

    // Append (and flatten) elements.
    // http://stackoverflow.com/questions/1319903/how-to-flatten-a-multidimensional-array
    $default_options = $this->getDefaultExportOptions();
    array_walk_recursive($export_values['elements'], function($item, $key) use (&$values, $default_options) {
      if (isset($default_options[$key])) {
        $values[$key] = $item;
      }
    });

    return $values;
  }

  /****************************************************************************/
  // Generate and write.
  /****************************************************************************/

  /**
   * Generate YAML form submission as a CSV and write it to a temp file.
   *
   * @param \Drupal\yamlform\YamlFormInterface $yamlform
   *   A YAML form.
   * @param array $export_options
   *   An associative array of export options generated via the
   *   Drupal\yamlform\Form\YamlFormResultsExportForm.
   */
  public function generate(YamlFormInterface $yamlform, array $export_options) {
    $field_definitions = $this->getFieldDefinitions($export_options);
    $elements = $this->getElements($yamlform, $export_options);

    // Convert tabs delimiter.
    if ($export_options['delimiter'] == '\t') {
      $export_options['delimiter'] = "\t";
    }

    // Build header and add to CSV.
    $this->writeHeader($yamlform, $field_definitions, $elements, $export_options);

    // Build records and add to CSV.
    $entity_ids = $this->getQuery($yamlform, $export_options)->execute();
    $yamlform_submissions = YamlFormSubmission::loadMultiple($entity_ids);
    $this->writeRecords($yamlform, $yamlform_submissions, $field_definitions, $elements, $export_options);
  }

  /**
   * Write YAML form results header to CSV file.
   *
   * @param \Drupal\yamlform\YamlFormInterface $yamlform
   *   The YAML form.
   * @param array $field_definitions
   *   An associative array containing YAML form submission field definitions.
   * @param array $elements
   *   An associative array containing YAML form elements.
   * @param array $export_options
   *   An associative array of export options.
   */
  public function writeHeader(YamlFormInterface $yamlform, array $field_definitions, array $elements, array $export_options) {
    $file_path = $this->getFilePath($yamlform);

    // Build header and add to CSV.
    $handle = fopen($file_path, 'w');
    $header = $this->buildHeader($field_definitions, $elements, $export_options);
    fputcsv($handle, $header, $export_options['delimiter']);
    fclose($handle);
  }

  /**
   * Write YAML form results header to CSV file.
   *
   * @param \Drupal\yamlform\YamlFormInterface $yamlform
   *   The YAML form.
   * @param \Drupal\yamlform\YamlFormSubmissionInterface $yamlform_submission
   *   A YAML form submission.
   * @param array $field_definitions
   *   An associative array containing YAML form submission field definitions.
   * @param array $elements
   *   An associative array containing YAML form elements.
   * @param array $export_options
   *   An associative array of export options.
   */
  public function writeRecords(YamlFormInterface $yamlform, array $yamlform_submissions, array $field_definitions, array $elements, array $export_options) {
    $file_path = $this->getFilePath($yamlform);
    $handle = fopen($file_path, 'a');
    foreach ($yamlform_submissions as $yamlform_submission) {
      $record = $this->buildRecord($yamlform_submission, $field_definitions, $elements, $export_options);
      fputcsv($handle, $record, $export_options['delimiter']);
    }
    fclose($handle);
  }

  /****************************************************************************/
  // Field definitions, elements, and query.
  /****************************************************************************/

  /**
   * Get YAML form submission field definitions.
   *
   * @param array $export_options
   *   An associative array of export options.
   *
   * @return array
   *   An associative array containing YAML form submission field definitions.
   */
  public function getFieldDefinitions(array $export_options) {
    $field_definitions = $this->entityStorage;
    $field_definitions = array_diff_key($field_definitions, $export_options['excluded_columns']);

    // Add custom entity reference field definitions which rely on the
    // entity type and entity id.
    if ($export_options['entity_reference_format'] == 'link' && isset($field_definitions['entity_type']) && isset($field_definitions['entity_id'])) {
      $field_definitions['entity_title'] = [
        'name' => 'entity_title',
        'title' => t('Submitted to: Entity title'),
        'type' => 'entity_title',
      ];
      $field_definitions['entity_url'] = [
        'name' => 'entity_url',
        'title' => t('Submitted to: Entity URL'),
        'type' => 'entity_url',
      ];
    }
    return $field_definitions;
  }

  /**
   * Get YAML form elements.
   *
   * @param \Drupal\yamlform\YamlFormInterface $yamlform
   *   A YAML form.
   * @param array $export_options
   *   An associative array of export options.
   *
   * @return array
   *   An associative array containing YAML form elements keyed by name.
   */
  public function getElements(YamlFormInterface $yamlform, array $export_options) {
    $input_columns = $yamlform->getElements();
    return array_diff_key($input_columns, $export_options['excluded_columns']);
  }

  /**
   * Get YAML form submission query for specified YAMl form and export options.
   *
   * @param \Drupal\yamlform\YamlFormInterface $yamlform
   *   A YAML form.
   * @param array $export_options
   *   An associative array of export options.
   *
   * @return \Drupal\Core\Entity\Query\QueryInterface
   *   A YAML form submission entity query.
   */
  public function getQuery(YamlFormInterface $yamlform, array $export_options) {
    $query = $this->queryFactory->get('yamlform_submission')->condition('yamlform_id', $yamlform->id());

    // Filter by sid or date range.
    switch ($export_options['range_type']) {
      case 'sid':
        if ($export_options['range_start']) {
          $query->condition('sid', $export_options['range_start'], '>=');
        }
        if ($export_options['range_end']) {
          $query->condition('sid', $export_options['range_end'], '<=');
        }
        break;

      case 'date':
        if ($export_options['range_start']) {
          $query->condition('created', strtotime($export_options['range_start']), '>=');
        }
        if ($export_options['range_end']) {
          $query->condition('created', strtotime($export_options['range_end']), '<=');
        }
        break;
    }

    // Filter by (completion) state.
    switch ($export_options['state']) {
      case 'draft';
        $query->condition('in_draft', 1);
        break;

      case 'completed';
        $query->condition('in_draft', 0);
        break;

    }

    // Filter by latest.
    if ($export_options['range_type'] == 'latest' && $export_options['range_latest']) {
      $query->range(0, $export_options['range_latest']);
      $query->sort('sid', 'DESC');
    }
    else {
      $query->sort('sid');
    }

    return $query;
  }

  /****************************************************************************/
  // Header.
  /****************************************************************************/

  /**
   * Build CSV header using YAML form submission field definitions and YAML form input columns.
   *
   * @param array $field_definitions
   *   An associative array containing YAML form submission field definitions.
   * @param array $elements
   *   An associative array containing YAML form elements.
   * @param array $export_options
   *   An associative array of export options.
   *
   * @return array
   *   An array containing the CSV header.
   */
  protected function buildHeader(array $field_definitions, array $elements, array $export_options) {
    $header = [];
    foreach ($field_definitions as $field_definition) {
      // Build a YAML form element for each field definition so that we can
      // use YamlFormElement::buildExportHeader(array $element, $export_options).
      $element = [
        '#type' => ($field_definition['type'] == 'entity_reference') ? 'entity_autocomplete' : 'element',
        '#title' => (string) $field_definition['title'],
        '#key' => (string) $field_definition['name'],
      ];
      $header = array_merge($header, $this->yamlformElementManager->invokeMethod('buildExportHeader', $element, $export_options));
    }

    // Build input columns headers.
    foreach ($elements as $element) {
      $header = array_merge($header, $this->yamlformElementManager->invokeMethod('buildExportHeader', $element, $export_options));
    }
    return $header;
  }

  /****************************************************************************/
  // Record.
  /****************************************************************************/

  /**
   * Build CSV record using YAML form submission, field definitions, and input columns.
   *
   * @param \Drupal\yamlform\YamlFormSubmissionInterface $yamlform_submission
   *   A YAML form submission.
   * @param array $field_definitions
   *   An associative array containing YAML form submission field definitions.
   * @param array $elements
   *   An associative array containing YAML form elements.
   * @param array $export_options
   *   An associative array of export options.
   *
   * @return array
   *   An array containing the CSV record.
   */
  protected function buildRecord(YamlFormSubmissionInterface $yamlform_submission, array $field_definitions, array $elements, array $export_options) {
    $record = [];

    // Build record field definition columns.
    foreach ($field_definitions as $field_definition) {
      $this->formatRecordFieldDefinitionValue($record, $yamlform_submission, $field_definition, $export_options);
    }

    // Build record input columns.
    $data = $yamlform_submission->getData();
    foreach ($elements as $column_name => $element) {
      $value = (isset($data[$column_name])) ? $data[$column_name] : '';
      $record = array_merge($record, $this->yamlformElementManager->invokeMethod('buildExportRecord', $element, $value, $export_options));
    }
    return $record;
  }

  /**
   * Get the field definition value from a YAML form submission entity.
   *
   * @param array $record
   *   The record to be added to the CSV export.
   * @param \Drupal\yamlform\YamlFormSubmissionInterface $yamlform_submission
   *   A YAML form submission.
   * @param array $field_definition
   *   The field definition for the value.
   * @param array $export_options
   *   An associative array of export options.
   */
  protected function formatRecordFieldDefinitionValue(array &$record, YamlFormSubmissionInterface $yamlform_submission, array $field_definition, array $export_options) {
    $field_name = $field_definition['name'];
    $field_type = $field_definition['type'];
    switch ($field_type) {
      case 'created':
      case 'changed':
        $record[] = date('c', $yamlform_submission->get($field_name)->value);
        break;

      case 'entity_reference':
        $element = [
          '#type' => 'entity_autocomplete',
          '#target_type' => $field_definition['target_type'],
        ];
        $value = $yamlform_submission->get($field_name)->target_id;
        $record = array_merge($record, $this->yamlformElementManager->invokeMethod('buildExportRecord', $element, $value, $export_options));
        break;

      case 'entity_url':
      case 'entity_title':
        if (empty($yamlform_submission->entity_type->value) || empty($yamlform_submission->entity_id->value)) {
          $record[] = '';
          break;
        }

        $entity = entity_load($yamlform_submission->entity_type->value, $yamlform_submission->entity_id->value);
        if ($entity) {
          $record[] = ($field_type == 'entity_url') ? $entity->toUrl()->setOption('absolute', TRUE)->toString() : $entity->label();
        }
        else {
          $record[] = '';
        }
        break;

      default:
        $record[] = $yamlform_submission->get($field_name)->value;
        break;
    }
  }

  /****************************************************************************/
  // Summary and download.
  /****************************************************************************/

  /**
   * Total number of submissions to be exported.
   *
   * @param \Drupal\yamlform\YamlFormInterface $yamlform
   *   A YAML form.
   * @param array $export_options
   *   An associative array of export options.
   *
   * @return int
   *   The total number of submissions to be exported.
   */
  public function getTotal(YamlFormInterface $yamlform, array $export_options) {
    return $this->getQuery($yamlform, $export_options)->count()->execute();
  }

  /**
   * Get the number of submissions to be exported with each batch.
   *
   * @return int
   *   Number of submissions to be exported with each batch.
   */
  public function getBatchLimit() {
    return $this->configFactory->get('yamlform.settings')->get('batch.export_limit') ?: 500;
  }

  /**
   * Determine if YAML form submissions must be exported using batch processing.
   *
   * @param \Drupal\yamlform\YamlFormInterface $yamlform
   *   A YAML form.
   *
   * @return bool
   *   TRUE if YAML form submissions must be exported using batch processing.
   */
  public function requiresBatch(YamlFormInterface $yamlform) {
    return ($this->getTotal($yamlform, $this->getDefaultExportOptions()) > $this->getBatchLimit()) ? TRUE : FALSE;
  }

  /**
   * Get CSV file name and path for a YAML form.
   *
   * @param \Drupal\yamlform\YamlFormInterface $yamlform
   *   A YAML form.
   *
   * @return string
   *   CSV file name and path for a YAML form
   */
  public function getFilePath(YamlFormInterface $yamlform) {
    return file_directory_temp() . '/' . $yamlform->id() . '.csv';
  }

}
