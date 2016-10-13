<?php

namespace Drupal\yamlform;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Archiver\ArchiveTar;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\yamlform\Entity\YamlFormSubmission;

/**
 * Form submission exporter.
 */
class YamlFormSubmissionExporter implements YamlFormSubmissionExporterInterface {

  use StringTranslationTrait;

  /**
   * The configuration object factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Form submission storage.
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
   * Form element manager.
   *
   * @var \Drupal\yamlform\YamlFormElementManagerInterface
   */
  protected $elementManager;

  /**
   * The form.
   *
   * @var \Drupal\yamlform\YamlFormInterface
   */
  protected $yamlform;

  /**
   * The source entity.
   *
   * @var \Drupal\Core\Entity\EntityInterface
   */
  protected $sourceEntity;

  /**
   * Default export options.
   *
   * @var array
   */
  protected $defaultOptions;

  /**
   * Form element types.
   *
   * @var array
   */
  protected $elementTypes;

  /**
   * Constructs a YamlFormSubmissionExporter object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The configuration object factory.
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager.
   * @param \Drupal\Core\Entity\Query\QueryFactory $query_factory
   *   The entity query factory.
   * @param \Drupal\yamlform\YamlFormElementManagerInterface $element_manager
   *   The form element manager.
   */
  public function __construct(ConfigFactoryInterface $config_factory, EntityManagerInterface $entity_manager, QueryFactory $query_factory, YamlFormElementManagerInterface $element_manager) {
    $this->configFactory = $config_factory;
    $this->entityStorage = $entity_manager->getStorage('yamlform_submission');
    $this->queryFactory = $query_factory;
    $this->elementManager = $element_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function setYamlForm(YamlFormInterface $yamlform = NULL) {
    $this->yamlform = $yamlform;
    $this->defaultOptions = NULL;
    $this->elementTypes = NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getYamlForm() {
    return $this->yamlform;
  }

  /**
   * {@inheritdoc}
   */
  public function setSourceEntity(EntityInterface $entity = NULL) {
    $this->sourceEntity = $entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getSourceEntity() {
    return $this->sourceEntity;
  }

  /**
   * {@inheritdoc}
   */
  public function getYamlFormOptions() {
    $name = $this->getYamlFormOptionsName();
    return $this->getYamlForm()->getState($name, []);
  }

  /**
   * {@inheritdoc}
   */
  public function setYamlFormOptions(array $options = []) {
    $name = $this->getYamlFormOptionsName();
    $this->getYamlForm()->setState($name, $options);
  }

  /**
   * {@inheritdoc}
   */
  public function deleteYamlFormOptions() {
    $name = $this->getYamlFormOptionsName();
    $this->getYamlForm()->deleteState($name);
  }

  /**
   * Get options name for current form and source entity.
   *
   * @return string
   *   Settings name as 'yamlform.export.{entity_type}.{entity_id}.
   */
  protected function getYamlFormOptionsName() {
    if ($entity = $this->getSourceEntity()) {
      return 'results.export.' . $entity->getEntityTypeId() . '.' . $entity->id();
    }
    else {
      return 'results.export';
    }
  }

  /****************************************************************************/
  // Default options and form.
  /****************************************************************************/

  /**
   * {@inheritdoc}
   */
  public function getDefaultExportOptions() {
    if (isset($this->defaultOptions)) {
      return $this->defaultOptions;
    }

    $this->defaultOptions = [
      'delimiter' => ',',
      'header_format' => 'label',
      'header_prefix' => TRUE,
      'header_prefix_label_delimiter' => ': ',
      'header_prefix_key_delimiter' => '__',
      'excluded_columns' => [
        'uuid' => 'uuid',
        'token' => 'token',
        'yamlform_id' => 'yamlform_id',
      ],
      'entity_type' => '',
      'entity_id' => '',
      'range_type' => 'all',
      'range_latest' => '',
      'range_start' => '',
      'range_end' => '',
      'state' => 'all',
      'sticky' => '',
      'download' => TRUE,
      'files' => FALSE,
    ];

    // Append element handler default options.
    $element_types = $this->getYamlFormElementTypes();
    $element_handlers = $this->elementManager->getInstances();
    foreach ($element_handlers as $element_type => $element_handler) {
      if (empty($element_types) || isset($element_types[$element_type])) {
        $this->defaultOptions += $element_handler->getExportDefaultOptions();
      }
    }

    return $this->defaultOptions;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array &$form, FormStateInterface $form_state) {
    $yamlform = $this->getYamlForm();
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
      '#description' => $this->t('This is the delimiter used in the CSV/TSV file when downloading form results. Using tabs in the export is the most reliable method for preserving non-latin characters. You may want to change this to another character depending on the program with which you anticipate importing results.'),
      '#required' => TRUE,
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

    $form['export']['format']['header_format'] = [
      '#type' => 'radios',
      '#title' => $this->t('Column header format'),
      '#description' => $this->t('Choose whether to show the element label or element key in each column header.'),
      '#required' => TRUE,
      '#options' => [
        'label' => $this->t('Element titles (label)'),
        'key' => $this->t('Element keys (key)'),
      ],
      '#default_value' => $default_values['header_format'],
    ];

    $form['export']['format']['header_prefix'] = [
      '#type' => 'checkbox',
      '#title' => $this->t("Include an element's title with all sub elements and values in each column header."),
      '#return_value' => TRUE,
      '#default_value' => $default_values['header_prefix'],
    ];
    $form['export']['format']['header_prefix_label_delimiter'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Column header label delimiter'),
      '#required' => TRUE,
      '#default_value' => $default_values['header_prefix_label_delimiter'],
    ];
    $form['export']['format']['header_prefix_key_delimiter'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Column header key delimiter'),
      '#required' => TRUE,
      '#default_value' => $default_values['header_prefix_key_delimiter'],
    ];
    if ($yamlform) {
      $form['export']['format']['header_prefix_label_delimiter']['#states'] = [
        'visible' => [
          ':input[name="export[format][header_prefix]"]' => ['checked' => TRUE],
          ':input[name="export[format][header_format]"]' => ['value' => 'label'],
        ],
      ];
      $form['export']['format']['header_prefix_key_delimiter']['#states'] = [
        'visible' => [
          ':input[name="export[format][header_prefix]"]' => ['checked' => TRUE],
          ':input[name="export[format][header_format]"]' => ['value' => 'key'],
        ],
      ];
    }

    // Build element specific export forms.
    // Grouping everything in $form['export']['elements'] so that element handlers can
    // assign #weight to its export options form.
    $form['export']['elements'] = [];
    $element_types = $this->getYamlFormElementTypes();
    $element_handlers = $this->elementManager->getInstances();
    foreach ($element_handlers as $element_type => $element_handler) {
      if (empty($element_types) || isset($element_types[$element_type])) {
        $element_handler->buildExportOptionsForm($form['export']['elements'], $form_state, $default_values);
      }
    }

    // All the remain options are only applicable to a form's export.
    // @see Drupal\yamlform\Form\YamlFormResultsExportForm
    if (!$yamlform) {
      return;
    }

    // Elements.
    $form['export']['columns'] = [
      '#type' => 'details',
      '#title' => t('Column options'),
    ];
    $form['export']['columns']['excluded_columns'] = [
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
      '#access' => !$this->requiresBatch(),
    ];
    $form['export']['download']['files'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Download files'),
      '#description' => $this->t('If checked, the exported CSV file and any submission file uploads will be download in a gzipped tar file.'),
      '#access' => $yamlform->hasManagedFile(),
      '#states' => [
        'invisible' => [
          ':input[name="export[download][download]"]' => ['checked' => FALSE],
        ],
      ],
      '#default_value' => ($yamlform->hasManagedFile()) ? $default_values['files'] : 0,
    ];

    $source_entity = $this->getSourceEntity();
    if (!$source_entity) {
      $entity_types = $this->entityStorage->getSourceEntityTypes($yamlform);
      if ($entity_types) {
        $form['export']['download']['submitted'] = [
          '#type' => 'item',
          '#title' => $this->t('Submitted to'),
          '#description' => $this->t('Select the entity type and then enter the entity id.'),
          '#field_prefix' => '<div class="container-inline">',
          '#field_suffix' => '</div>',
        ];
        $form['export']['download']['submitted']['entity_type'] = [
          '#type' => 'select',
          '#title' => $this->t('Entity type'),
          '#title_display' => 'Invisible',
          '#options' => ['' => $this->t('All')] + $entity_types,
          '#default_value' => $default_values['entity_type'],
        ];
        $form['export']['download']['submitted']['entity_id'] = [
          '#type' => 'number',
          '#title' => $this->t('Entity id'),
          '#title_display' => 'Invisible',
          '#min' => 1,
          '#size' => 10,
          '#default_value' => $default_values['entity_id'],
          '#states' => [
            'invisible' => [
              ':input[name="export[download][submitted][entity_type]"]' => ['value' => ''],
            ],
          ],
        ];
      }
    }

    $form['export']['download']['range_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Limit to'),
      '#options' => [
        'all' => t('All'),
        'latest' => t('Latest'),
        'serial' => t('Submission number'),
        'sid' => t('Submission ID'),
        'date' => t('Date'),
      ],
      '#default_value' => $default_values['range_type'],
    ];
    $form['export']['download']['latest'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['container-inline']],
      '#states' => [
        'visible' => [
          ':input[name="export[download][range_type]"]' => ['value' => 'latest'],
        ],
      ],
      'range_latest' => [
        '#type' => 'number',
        '#title' => $this->t('Number of submissions'),
        '#min' => 1,
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
        '#attributes' => ['class' => ['container-inline']],
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

    $form['export']['download']['sticky'] = [
      '#type' => 'checkbox',
      '#title' => t('Starred/flagged submissions'),
      '#description' => $this->t('If checked only starred/flagged submissions will be downloaded. If unchecked all submissions will downloaded.'),
      '#default_value' => $default_values['sticky'],
    ];

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
   * {@inheritdoc}
   */
  public function getFormValues(FormStateInterface $form_state) {
    $export_values = $form_state->getValue('export');
    $values = [];

    // Append download/range type, submitted, and sticky.
    if (isset($export_values['download'])) {
      $values['state'] = $export_values['download']['state'];
      $values['download'] = $export_values['download']['download'];
      $values['files'] = $export_values['download']['files'];
      $values['sticky'] = $export_values['download']['sticky'];
      if (!empty($export_values['download']['submitted']['entity_type'])) {
        $values += $export_values['download']['submitted'];
      }
      if (isset($export_values['download']['range_type'])) {
        $range_type = $export_values['download']['range_type'];
        $values['range_type'] = $range_type;
        if (isset($export_values['download'][$range_type])) {
          $values += $export_values['download'][$range_type];
        }
      }
    }

    // Append format.
    if (isset($export_values['format'])) {
      $values += $export_values['format'];
    }

    // Append columns.
    if (isset($export_values['columns'])) {
      $values += $export_values['columns'];
    }

    // Append (and flatten) elements.
    // http://stackoverflow.com/questions/1319903/how-to-flatten-a-multidimensional-array
    $default_options = $this->getDefaultExportOptions();
    array_walk_recursive($export_values['elements'], function ($item, $key) use (&$values, $default_options) {
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
   * {@inheritdoc}
   */
  public function generate(array $export_options) {
    $field_definitions = $this->getFieldDefinitions($export_options);
    $elements = $this->getElements($export_options);

    // Convert tabs delimiter.
    if ($export_options['delimiter'] == '\t') {
      $export_options['delimiter'] = "\t";
    }

    // Build header and add to CSV.
    $this->writeHeader($field_definitions, $elements, $export_options);

    // Build records and add to CSV.
    $entity_ids = $this->getQuery($export_options)->execute();
    $yamlform_submissions = YamlFormSubmission::loadMultiple($entity_ids);
    $this->writeRecords($yamlform_submissions, $field_definitions, $elements, $export_options);
  }

  /**
   * {@inheritdoc}
   */
  public function writeHeader(array $field_definitions, array $elements, array $export_options) {
    // If building a new archive make sure to delete the exist archive.
    if ($this->isArchive($export_options)) {
      @unlink($this->getArchiveFilePath($export_options));
    }

    $file_path = $this->getCsvFilePath($export_options);

    $handle = fopen($file_path, 'w');
    $header = $this->buildHeader($field_definitions, $elements, $export_options);
    fputcsv($handle, $header, $export_options['delimiter']);
    fclose($handle);
  }

  /**
   * {@inheritdoc}
   */
  public function writeRecords(array $yamlform_submissions, array $field_definitions, array $elements, array $export_options) {
    $yamlform = $this->getYamlForm();

    $is_archive = $this->isArchive($export_options);
    if ($is_archive) {
      $files_directory = drupal_realpath('public://yamlform/' . $yamlform->id());
      $files = [];
    }

    $file_path = $this->getCsvFilePath($export_options);
    $handle = fopen($file_path, 'a');
    foreach ($yamlform_submissions as $yamlform_submission) {
      if ($is_archive && file_exists($files_directory . '/' . $yamlform_submission->id())) {
        $files[] = $files_directory . '/' . $yamlform_submission->id();
      }

      $record = $this->buildRecord($yamlform_submission, $field_definitions, $elements, $export_options);
      fputcsv($handle, $record, $export_options['delimiter']);
    }
    fclose($handle);

    if ($is_archive && $files) {
      $archiver = new ArchiveTar($this->getArchiveFilePath($export_options), 'gz');
      $archiver->addModify($files, $this->getBaseFileName(), $files_directory);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function writeCsvToArchive(array $export_options) {
    $csv_file_path = $this->getCsvFilePath($export_options);
    $archive_file_path = $this->getArchiveFilePath($export_options);

    $archiver = new ArchiveTar($archive_file_path, 'gz');
    $archiver->addModify($csv_file_path, $this->getBaseFileName(), $this->getFileTempDirectory());

    @unlink($csv_file_path);
  }

  /****************************************************************************/
  // Field definitions, elements, and query.
  /****************************************************************************/

  /**
   * {@inheritdoc}
   */
  public function getFieldDefinitions(array $export_options) {
    $field_definitions = $this->entityStorage->getFieldDefinitions();
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
   * {@inheritdoc}
   */
  public function getElements(array $export_options) {
    $yamlform = $this->getYamlForm();
    $element_columns = $yamlform->getElementsFlattenedAndHasValue();
    return array_diff_key($element_columns, $export_options['excluded_columns']);
  }

  /**
   * {@inheritdoc}
   */
  public function getQuery(array $export_options) {
    $yamlform = $this->getYamlForm();
    $source_entity = $this->getSourceEntity();

    $query = $this->queryFactory->get('yamlform_submission')->condition('yamlform_id', $yamlform->id());

    // Filter by source entity or submitted to.
    if ($source_entity) {
      $query->condition('entity_type', $source_entity->getEntityTypeId());
      $query->condition('entity_id', $source_entity->id());
    }
    elseif ($export_options['entity_type']) {
      $query->condition('entity_type', $export_options['entity_type']);
      if ($export_options['entity_id']) {
        $query->condition('entity_id', $export_options['entity_id']);
      }
    }

    // Filter by sid or date range.
    switch ($export_options['range_type']) {
      case 'serial':
        if ($export_options['range_start']) {
          $query->condition('serial', $export_options['range_start'], '>=');
        }
        if ($export_options['range_end']) {
          $query->condition('serial', $export_options['range_end'], '<=');
        }
        break;

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

    // Filter by sticky.
    if ($export_options['sticky']) {
      $query->condition('sticky', 1);
    }

    // Filter by latest.
    if ($export_options['range_type'] == 'latest' && $export_options['range_latest']) {
      // Clone the query and use it to get latest sid starting sid.
      $latest_query = clone $query;
      $latest_query->sort('sid', 'DESC');
      $latest_query->range(0, (int) $export_options['range_latest']);
      if ($latest_query_entity_ids = $latest_query->execute()) {
        $query->condition('sid', end($latest_query_entity_ids), '>=');
      }
    }

    // Sort by sid with the oldest one first.
    $query->sort('sid', 'ASC');

    return $query;
  }

  /****************************************************************************/
  // Header.
  /****************************************************************************/

  /**
   * Build CSV header using form submission field definitions and form element columns.
   *
   * @param array $field_definitions
   *   An associative array containing form submission field definitions.
   * @param array $elements
   *   An associative array containing form elements.
   * @param array $export_options
   *   An associative array of export options.
   *
   * @return array
   *   An array containing the CSV header.
   */
  protected function buildHeader(array $field_definitions, array $elements, array $export_options) {
    $header = [];
    foreach ($field_definitions as $field_definition) {
      // Build a form element for each field definition so that we can
      // use YamlFormElement::buildExportHeader(array $element, $export_options).
      $element = [
        '#type' => ($field_definition['type'] == 'entity_reference') ? 'entity_autocomplete' : 'element',
        '#admin_title' => '',
        '#title' => (string) $field_definition['title'],
        '#yamlform_key' => (string) $field_definition['name'],
      ];
      $header = array_merge($header, $this->elementManager->invokeMethod('buildExportHeader', $element, $export_options));
    }

    // Build element columns headers.
    foreach ($elements as $element) {
      $header = array_merge($header, $this->elementManager->invokeMethod('buildExportHeader', $element, $export_options));
    }
    return $header;
  }

  /****************************************************************************/
  // Record.
  /****************************************************************************/

  /**
   * Build CSV record using form submission, field definitions, and element columns.
   *
   * @param \Drupal\yamlform\YamlFormSubmissionInterface $yamlform_submission
   *   A form submission.
   * @param array $field_definitions
   *   An associative array containing form submission field definitions.
   * @param array $elements
   *   An associative array containing form elements.
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

    // Build record element columns.
    $data = $yamlform_submission->getData();
    foreach ($elements as $column_name => $element) {
      $value = (isset($data[$column_name])) ? $data[$column_name] : '';
      $record = array_merge($record, $this->elementManager->invokeMethod('buildExportRecord', $element, $value, $export_options));
    }
    return $record;
  }

  /**
   * Get the field definition value from a form submission entity.
   *
   * @param array $record
   *   The record to be added to the CSV export.
   * @param \Drupal\yamlform\YamlFormSubmissionInterface $yamlform_submission
   *   A form submission.
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
        $record = array_merge($record, $this->elementManager->invokeMethod('buildExportRecord', $element, $value, $export_options));
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

  /**
   * Get element types from a form.
   *
   * @return array
   *   An array of element types from a form.
   */
  protected function getYamlFormElementTypes() {
    if (isset($this->elementTypes)) {
      return $this->elementTypes;
    }
    // If the form is not set which only occurs on the admin settings form,
    // return an empty array.
    if (!isset($this->yamlform)) {
      return [];
    }

    $this->elementTypes = [];
    $elements = $this->yamlform->getElementsDecodedAndFlattened();
    // Always include 'entity_autocomplete' export settings which is used to
    // expand a form submission's entity references.
    $this->elementTypes['entity_autocomplete'] = 'entity_autocomplete';
    foreach ($elements as $element) {
      if (isset($element['#type'])) {
        $type = $this->elementManager->getElementPluginId($element);
        $this->elementTypes[$type] = $type;
      }
    }
    return $this->elementTypes;
  }

  /****************************************************************************/
  // Summary and download.
  /****************************************************************************/

  /**
   * {@inheritdoc}
   */
  public function getTotal(array $export_options) {
    return $this->getQuery($export_options)->count()->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function getBatchLimit() {
    return $this->configFactory->get('yamlform.settings')->get('batch.default_batch_export_size') ?: 500;
  }

  /**
   * {@inheritdoc}
   */
  public function requiresBatch() {
    return ($this->getTotal($this->getDefaultExportOptions()) > $this->getBatchLimit()) ? TRUE : FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getFileTempDirectory() {
    return file_directory_temp();
  }

  /**
   * {@inheritdoc}
   */
  protected function getBaseFileName() {
    $yamlform = $this->getYamlForm();
    $source_entity = $this->getSourceEntity();
    if ($source_entity) {
      return $yamlform->id() . '.' . $source_entity->getEntityTypeId() . '.' . $source_entity->id();
    }
    else {
      return $yamlform->id();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getCsvFilePath(array $export_options) {
    return $this->getFileTempDirectory() . '/' . $this->getCsvFileName($export_options);
  }

  /**
   * {@inheritdoc}
   */
  public function getCsvFileName(array $export_options) {
    switch ($export_options['delimiter']) {
      case '\t':
      case "\t":
        return $this->getBaseFileName() . '.tsv';

      default:
        return $this->getBaseFileName() . '.csv';
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getArchiveFilePath(array $export_options) {
    return $this->getFileTempDirectory() . '/' . $this->getArchiveFileName($export_options);
  }

  /**
   * {@inheritdoc}
   */
  public function getArchiveFileName(array $export_options) {
    return $this->getBaseFileName() . '.tar.gz';
  }

  /**
   * {@inheritdoc}
   */
  public function isArchive(array $export_options) {
    return ($export_options['download'] && $export_options['files']);
  }

  /**
   * {@inheritdoc}
   */
  public function isBatch(array $export_options) {
    return ($this->isArchive($export_options) || ($this->getTotal($export_options) >= $this->getBatchLimit()));
  }

}
