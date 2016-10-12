<?php

/**
 * @file
 * YAML Form module drush commands.
 */

use Drupal\yamlform\Controller\YamlFormResultsExportController;
use Drupal\yamlform\Entity\YamlForm;
use Drupal\yamlform\Form\YamlFormResultsClearForm;
use Drupal\yamlform\Form\YamlFormSubmissionsPurgeForm;
use Drupal\yamlform\Utility\YamlFormTidy;

/**
 * Implements hook_drush_command().
 */
function yamlform_drush_command() {
  $items = [];

  /* Submissions */

  $items['yamlform-export'] = [
    'description' => 'Exports form submissions to a file.',
    'core' => ['8+'],
    'bootstrap' => DRUSH_BOOTSTRAP_DRUPAL_SITE,
    'arguments' => [
      'yamlform' => 'The form ID you want to export (required unless --entity-type and --entity-id are specified)',
    ],
    'options' => [
      'delimiter' => 'Delimiter between columns (defaults to site-wide setting). This option may need to be wrapped in quotes. i.e. --delimiter="\t".',
      'header-keys' => 'Set to "label" (default) or "key"',
      'options-item-format' => 'Set to "label" (default) or "key". Set to "key" to print select list values by their keys instead of labels.',
      'options-format' => 'Set to "separate" (default) or "compact" to determine how select list values are exported.',
      'entity-reference-format' => 'Set to "link" (default) or "id" to determine how entity references are exported.',
      'excluded-columns' => 'Comma-separated list of component IDs or form keys to exclude.',
      'entity-type' => 'The entity type to which this submission was submitted from.',
      'entity-id' => 'The ID of the entity of which this form submission was submitted from.',
      'range-type' => 'Range of submissions to export: "all", "latest", "serial", "sid", or "date".',
      'range-latest' => 'Integer specifying the latest X submissions will be downloaded. Used if "range-type" is "latest" or no other range options are provided.',
      'range-start' => 'The submission ID or start date at which to start exporting.',
      'range-end' => 'The submission ID or end date at which to end exporting.',
      'state' => 'Submission state to be included: "completed", "draft" or "all" (default).',
      'sticky' => 'Flagged/starred submission status.',
      'files' => 'Download files: "1" or "0" (default). If set to 1, the exported CSV file and any submission file uploads will be download in a gzipped tar file.',
      'destination' => 'The full path and filename in which the CSV or archive should be stored. If omitted the CSV file or archive will be outputted to the commandline.',
    ],
    'aliases' => ['yfx'],
  ];

  $items['yamlform-purge'] = [
    'description' => "Purge form submissions from the databases",
    'core' => ['8+'],
    'bootstrap' => DRUSH_BOOTSTRAP_DRUPAL_SITE,
    'arguments' => [
      'yamlform_id' => "A form machine name. If not provided, user may choose from a list of names.",
    ],
    'options' => [
      'all' => 'Flush all submissions',
      'entity-type' => 'The entity type for form submissions to be purged',
      'entity-id' => 'The ID of the entity for form submissions to be purged',
    ],
    'examples' => [
      'drush yamlform-purge' => 'Pick a form and then purge its submissions.',
      'drush yamlform-purge contact' => "Delete 'Contact' form submissions.",
      'drush yamlform-purge --all' => 'Purge all form submissions.',
    ],
    'aliases' => ['yfp'],
  ];

  /* Tidy */

  $items['yamlform-tidy'] = [
    'description' => "Tidy export form configuration files",
    'core' => ['8+'],
    'bootstrap' => DRUSH_BOOTSTRAP_DRUPAL_ROOT,
    'arguments' => [
      'module' => "The module that needs its YAML configuration files (config/install) tidied. (Defaults to yamlform)",
    ],
    'examples' => [
      'drush yamlform-tidy yamlform' => "Tidies YAML configuration files in 'yamlform/config' for the YAML Form module",
    ],
    'aliases' => ['yft'],
  ];

  /* Libraries */

  $items['yamlform-libraries-status'] = [
    'description' => 'Displays the status of third party libraries required by the YAML Form module.',
    'core' => ['8+'],
    'bootstrap' => DRUSH_BOOTSTRAP_DRUPAL_ROOT,
    'examples' => [
      'yamlform-libraries-status' => 'Displays the status of third party libraries required by the YAML Form module.',
    ],
    'aliases' => ['yfls'],
  ];

  $items['yamlform-libraries-download'] = [
    'description' => 'Download third party libraries required by the YAML Form module.',
    'core' => ['8+'],
    'bootstrap' => DRUSH_BOOTSTRAP_DRUPAL_ROOT,
    'examples' => [
      'yamlform-libraries-download' => 'Download third party libraries required by the YAML Form module.',
    ],
    'aliases' => ['yfld'],
  ];

  $items['yamlform-libraries-remove'] = [
    'description' => 'Removes all downloaded third party libraries required by the YAML Form module.',
    'core' => ['8+'],
    'bootstrap' => DRUSH_BOOTSTRAP_DRUPAL_ROOT,
    'examples' => [
      'yamlform-libraries-remove' => 'Removes all downloaded third party libraries required by the YAML Form module.',
    ],
    'aliases' => ['yflr'],
  ];

  /* Devel Generate */

  $items['yamlform-generate'] = [
    'callback' => 'drush_devel_generate',
    'callback arguments' => [
      'plugin_id' => 'yamlform_submission',
    ],
    'description' => 'Create submissions in specified form.',
    'arguments' => [
      'yamlform_id' => 'Form id into which new submissions will be inserted.',
      'num' => 'Number of submissions to insert. Defaults to 50.',
    ],
    'options' => [
      'kill' => 'Delete all submissions in specified form before generating.',
      'feedback' => 'An integer representing interval for insertion rate logging. Defaults to 1000',
      'entity-type' => 'The entity type to which this submission was submitted from.',
      'entity-id' => 'The ID of the entity of which this form submission was submitted from.',
    ],
    'aliases' => ['yfg'],
  ];

  return $items;
}

/**
 * Implements hook_drush_help().
 */
function yamlform_drush_help($section) {
  switch ($section) {
    case 'drush:yamlform-export':
      return dt('This command will export form submissions to a file.');

    case 'drush:yamlform-purge':
      return dt('This command will purge form submissions.');

    case 'drush:yamlform-libraries-status':
      return dt('This command displays the status of third party libraries required by the YAML Form module.');

    case 'drush:yamlform-libraries-download':
      return dt('This command downloads third party libraries required by the YAML Form module.');

    case 'drush:yamlform-libraries-remove':
      return dt('This command removes all downloaded third party libraries required by the YAML Form module.');

    case 'drush:yamlform-tidy':
      return dt('This command tidies exported YAML configuration files.') . "\n\n" .
        dt('Changes include...') . "\n" .
        dt('- Preserving newlines using pipe (|).') . "\n" .
        dt('- Removing returns after array dashes (-).');

    case 'drush:yamlform-generate':
      return dt('This command will create submissions in specified form.');

    case 'meta:yamlform:title':
      return dt('YAML Form commands');

    case 'meta:yamlform:summary':
      return dt('Enables the creation of forms and questionnaires.');
  }
}

/******************************************************************************/
// Export
/******************************************************************************/

/**
 * Implements drush_hook_COMMAND_validate().
 */
function drush_yamlform_export_validate($yamlform_id = NULL) {
  return _drush_yamlform_validate($yamlform_id);
}

/**
 * Implements drush_hook_COMMAND().
 */
function drush_yamlform_export($yamlform_id = NULL) {
  if (!$yamlform_id) {
    $yamlforms = array_keys(YamlForm::loadMultiple());
    $choices = array_combine($yamlforms, $yamlforms);
    $yamlform_id = drush_choice($choices, dt("Choose a form to export submissions from."));
    if ($yamlform_id === FALSE) {
      return drush_user_abort();
    }
  }

  $yamlform = YamlForm::load($yamlform_id);
  // @todd Determine if we should get source entity from options entity type
  // and id.
  $source_entity = NULL;

  /** @var \Drupal\yamlform\YamlFormSubmissionExporterInterface $exporter */
  $exporter = \Drupal::service('yamlform_submission.exporter');
  $exporter->setYamlForm($yamlform);
  $exporter->setSourceEntity($source_entity);

  // Get command options as export options.
  $export_options = drush_redispatch_get_options();
  // Convert dashes to underscores.
  foreach ($export_options as $key => $value) {
    unset($export_options[$key]);
    $export_options[str_replace('-', '_', $key)] = $value;
  }
  $export_options += $exporter->getDefaultExportOptions();

  YamlFormResultsExportController::batchSet($yamlform, $source_entity, $export_options);
  drush_backend_batch_process();

  $file_path = ($exporter->isArchive($export_options)) ? $exporter->getArchiveFilePath($export_options) : $exporter->getCsvFilePath($export_options);;
  if (isset($export_options['destination'])) {
    drush_print(dt('Created @destination', ['@destination' => $export_options['destination']]));
    file_unmanaged_copy($file_path, $export_options['destination'], FILE_EXISTS_REPLACE);
  }
  else {
    drush_print(file_get_contents($file_path));
  }
  @unlink($file_path);

  return NULL;
}

/******************************************************************************/
// Purge
/******************************************************************************/

/**
 * Implements drush_hook_COMMAND_validate().
 */
function drush_yamlform_purge_validate($yamlform_id = NULL) {
  // If form id is set to 'all' or  not included skip validation.
  if (drush_get_option('all') || $yamlform_id == NULL) {
    return;
  }

  return _drush_yamlform_validate($yamlform_id);
}

/**
 * Implements drush_hook_COMMAND().
 */
function drush_yamlform_purge($yamlform_id = NULL) {
  if (drush_get_option('all')) {
    $yamlform_id = 'all';
  }

  if (!$yamlform_id) {
    $yamlforms = array_keys(YamlForm::loadMultiple());
    $choices = array_combine($yamlforms, $yamlforms);
    $choices = array_merge(['all' => 'all'], $choices);
    $yamlform_id = drush_choice($choices, dt("Choose a form to purge submissions from."));
    if ($yamlform_id === FALSE) {
      return drush_user_abort();
    }
  }

  // Set the form.
  $yamlform = ($yamlform_id == 'all') ? NULL : YamlForm::load($yamlform_id);

  /** @var \Drupal\yamlform\YamlFormSubmissionStorageInterface $submission_storage */
  $submission_storage = \Drupal::entityTypeManager()->getStorage('yamlform_submission');
  /** @var \Drupal\yamlform\YamlFormRequestInterface $request_handler */
  $request_handler = \Drupal::service('yamlform.request');

  // Make sure there are submissions that need to be deleted.
  if (!$submission_storage->getTotal($yamlform)) {
    return drush_set_error(dt('There are no submissions that need to be deleted.'));
  }

  if (!$yamlform) {
    $submission_total = \Drupal::entityQuery('yamlform_submission')->count()->execute();
    $form_total = \Drupal::entityQuery('yamlform')->count()->execute();
    $t_args = [
      '@submission_total' => $submission_total,
      '@submissions' => \Drupal::translation()->formatPlural($submission_total, t('submission'), t('submissions')),
      '@form_total' => $form_total,
      '@forms' => \Drupal::translation()->formatPlural($form_total, t('form'), t('forms')),
    ];
    if (!drush_confirm(dt('Are you sure you want to delete @submission_total @submissions in @form_total @forms?', $t_args))) {
      return drush_user_abort();
    }
    $form = new YamlFormResultsClearForm($submission_storage, $request_handler);
    $form->batchSet();
    drush_backend_batch_process();
  }
  else {
    // Set source entity.
    if (drush_get_option('entity-type') && drush_get_option('entity-id')) {
      $source_entity = entity_load(drush_get_option('entity-type'), drush_get_option('entity-id'));
    }
    else {
      $source_entity = NULL;
    }

    $t_args = [
      '@title' => ($source_entity) ? $source_entity->label() : $yamlform->label(),
    ];
    if (!drush_confirm(dt("Are you sure you want to delete all submissions from '@title' form?", $t_args))) {
      return drush_user_abort();
    }
    $form = new YamlFormSubmissionsPurgeForm($submission_storage, $request_handler);
    $form->batchSet($yamlform, $source_entity);
    drush_backend_batch_process();
  }
}

/******************************************************************************/
// Tidy
/******************************************************************************/

/**
 * Implements drush_hook_COMMAND_validate().
 */
function drush_yamlform_tidy_validate($module = 'yamlform') {
  if (!file_exists(drupal_get_path('module', $module) . '/config')) {
    $t_args = [
      '@module' => $module,
      '@directory' => drupal_get_path('module', $module) . '/config',
    ];
    return drush_set_error(dt("@module does not contain a '@module/config' directory (@directory).", $t_args));
  }
}

/**
 * Implements drush_hook_COMMAND().
 */
function drush_yamlform_tidy($module = 'yamlform') {
  $file_directory_path = drupal_get_path('module', $module) . '/config';
  $files = file_scan_directory($file_directory_path, '/.*\.yml$/');
  drush_print(dt('Reviewing @count YAML configuration files for the @module.module.', ['@count' => count($files), '@module' => $module]));
  $total = 0;
  foreach ($files as $file) {
    $yaml = file_get_contents($file->uri);
    $tidied_yaml = YamlFormTidy::tidy($yaml);
    if ($yaml != $tidied_yaml) {
      drush_print(dt('Tidying @file...', ['@file' => $file->filename]));
      file_put_contents($file->uri, YamlFormTidy::tidy(file_get_contents($file->uri)));
      $total++;
    }
  }

  if ($total) {
    drush_print(dt('@total YAML file(s) tidied.', ['@total' => $total]));
  }
  else {
    drush_print(dt('No YAML files needed to be tidied.'));
  }
}

/******************************************************************************/
// Devel Generate.
/******************************************************************************/

/**
 * Implements drush_hook_COMMAND_validate().
 */
function drush_yamlform_generate_validate() {
  // Array of "Callback arguments" and "command line args".
  $params = func_get_args();
  $params[0] = 'yamlform_submission';
  _drush_plugin_validate($params);
}

/******************************************************************************/
// Libraries
/******************************************************************************/

/**
 * Implements drush_hook_COMMAND().
 */
function drush_yamlform_libraries_status() {
  module_load_include('install', 'yamlform');

  $requirements = _yamlform_libraries_requirements();

  foreach ($requirements as $requirement) {
    $library = $requirement['library'];
    drush_print($library['library_title'] . ' (' . $library['library_name'] . ') ' . $requirement['value']);
    // drush_print();
    drush_print(strip_tags($requirement['description']));
    drush_print();
  }
}

/**
 * Implements drush_hook_COMMAND().
 */
function drush_yamlform_libraries_remove($status = TRUE) {
  if ($status) {
    drush_print(dt('Beginning to remove libraries...'));
  }
  $removed = FALSE;
  $libraries = _yamlform_libraries_load();
  foreach ($libraries as $library_name => $library) {
    $library_path = '/' . $library['destination'] . '/' . $library['directory_name'];
    $library_exists = (file_exists(DRUPAL_ROOT . $library_path)) ? TRUE : FALSE;
    if ($library_exists) {
      $t_args = [
        '@name' => $library_name,
        '@path' => $library_path,
      ];
      if ($status) {
        drush_print(dt('@name removed from @path...', $t_args));
        drush_delete_dir(DRUPAL_ROOT . $library_path, TRUE);
      }
      $removed = TRUE;
    }
  }
  if ($status) {
    drupal_flush_all_caches();
  }
  return $removed;
}

/**
 * Implements drush_hook_COMMAND().
 */
function drush_yamlform_libraries_download() {
  // Remove all existing libraries.
  if (drush_yamlform_libraries_remove(FALSE)) {
    drush_print(dt('Removing existing libraries...'));
  }

  // Download libraries using drush.
  $commandline_args = [
    DRUPAL_ROOT . '/' . drupal_get_path('module', 'yamlform') . '/yamlform.libraries.make.yml',
    DRUPAL_ROOT,
  ];
  $commandline_options = ['no-core' => 'no-core'];
  drush_invoke_process('@self', 'make', $commandline_args, $commandline_options);
  drupal_flush_all_caches();
}

/******************************************************************************/
// Helper functions.
/******************************************************************************/

/**
 * Validate yamlform_id argument and source entity-type and entity-id options.
 */
function _drush_yamlform_validate($yamlform_id = NULL) {
  if (empty($yamlform_id)) {
    return drush_set_error(dt('Form id required'));
  }

  if (!empty($yamlform_id) && !YamlForm::load($yamlform_id)) {
    return drush_set_error(dt('Form @id not recognized.', ['@id' => $yamlform_id]));
  }

  $entity_type = drush_get_option('entity-type');
  $entity_id = drush_get_option('entity-id');
  if ($entity_type || $entity_id) {
    if (empty($entity_type)) {
      return drush_set_error(dt('Entity type is required when entity id is specified.'));
    }
    if (empty($entity_id)) {
      return drush_set_error(dt('Entity id is required when entity type is specified.'));
    }

    $dt_args = [
      '@yamlform_id' => $yamlform_id,
      '@entity_type' => $entity_type,
      '@entity_id' => $entity_id,
    ];

    $source_entity = entity_load($entity_type, $entity_id);
    if (!$source_entity) {
      return drush_set_error(dt('Unable to load @entity_type:@entity_id', $dt_args));
    }

    $dt_args['@title'] = $source_entity->label();

    if (empty($source_entity->yamlform) || empty($source_entity->yamlform->target_id)) {
      return drush_set_error(dt("'@title' (@entity_type:@entity_id) does not reference a form.", $dt_args));
    }

    if ($source_entity->yamlform->target_id != $yamlform_id) {
      return drush_set_error(dt("'@title' (@entity_type:@entity_id) does not have a '@yamlform_id' yamlform associated with it.", $dt_args));
    }
  }
  return NULL;
}
