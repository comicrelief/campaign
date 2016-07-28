<?php

/**
 * @file
 * YAML form drush commands.
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

  $items['yamlform-export'] = [
    'description' => 'Exports YAML form submissions to a file.',
    'core' => ['8+'],
    'bootstrap' => DRUSH_BOOTSTRAP_DRUPAL_SITE,
    'arguments' => [
      'yamlform' => 'The YAML form ID you want to export (required)',
    ],
    'options' => [
      'delimiter' => 'Delimiter between columns (defaults to site-wide setting). This option may need to be wrapped in quotes. i.e. --delimiter="\t".',
      'header-keys' => 'Set to "label" (default) or "key"',
      'select-keys' => 'Set to "label" (default) or "key". Set to "key" to print select list values by their keys instead of labels.',
      'select-format' => 'Set to "separate" (default) or "compact" to determine how select list values are exported.',
      'entity-reference-format' => 'Set to "link" (default) or "id" to determine how entity references are exported.',
      'excluded-components' => 'Comma-separated list of component IDs or form keys to exclude.',
      'range-type' => 'Range of submissions to export: "all", "latest", "sid", or "date".',
      'range-latest' => 'Integer specifying the latest X submissions will be downloaded. Used if "range-type" is "latest" or no other range options are provided.',
      'range-start' => 'The submission ID or start date at which to start exporting.',
      'range-end' => 'The submission ID or end date at which to end exporting.',
      'state' => 'Submission state to be included: "completed", "draft" or "all" (default).',
    ],
    'aliases' => ['yfx'],
  ];

  $items['yamlform-purge'] = [
    'description' => "Purge YAML form submissions from the databases",
    'core' => ['8+'],
    'bootstrap' => DRUSH_BOOTSTRAP_DRUPAL_SITE,
    'arguments' => [
      'yamlform_id' => "A YAML form machine name. If not provided, user may choose from a list of names.",
    ],
    'options' => [
      'all' => 'Flush all submissions',
    ],
    'examples' => [
      'drush yamlform-purge' => 'Pick a YAML form and then purge its submissions.',
      'drush yamlform-purge contact' => "Delete 'Contact' form submissions.",
      'drush yamlform-purge --all' => 'Purge all form submissions.',
    ],
    'aliases' => ['yfp'],
  ];

  $items['yamlform-tidy'] = [
    'description' => "Tidy export YAML form configuration files",
    'core' => ['8+'],
    'bootstrap' => DRUSH_BOOTSTRAP_DRUPAL_ROOT,
    'arguments' => [
      'module' => "The module that needs its YAML configuration file (config/install) tidied. (Default to yamlform)",
    ],
    'examples' => [
      'drush yamlform-tidy yamlform' => "Tidies YAML the YAML form module's configuration files in 'yamlform/config'",
    ],
    'aliases' => ['yft'],
  ];

  $items['yamlform-generate'] = [
    'callback' => 'drush_devel_generate',
    'callback arguments' => [
      'plugin_id' => 'yamlform_submission',
    ],
    'description' => 'Create submissions in specified YAML form.',
    'arguments' => [
      'yamlform_id' => 'YAML form id into which new submissions will be inserted.',
      'num' => 'Number of submissions to insert. Defaults to 50.',
    ],
    'options' => [
      'kill' => 'Delete all submissions in specified YAML form before generating.',
      'feedback' => 'An integer representing interval for insertion rate logging. Defaults to 1000',
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
      return dt('This command will export YAML form submissions to a file.');

    case 'drush:yamlform-purge':
      return dt('This command will purge YAML form submissions.');

    case 'drush:yamlform-tidy':
      return dt('This command tidies exported YAML configuration files.') . "\n\n" .
        dt('Changes include...') . "\n" .
        dt('- Preserving newlines using pipe (|).') . "\n" .
        dt('- Removing returns after array dashes (-).');

    case 'drush:yamlform-generate':
      return dt('This command will create submissions in specified YAML form.');

    case 'meta:yamlform:title':
      return dt('YAML form commands');

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
  if (!empty($yamlform_id) && !YamlForm::load($yamlform_id)) {
    return drush_set_error(dt('YAML form @id not recognized.', ['@id' => $yamlform_id]));
  }
}

/**
 * Implements drush_hook_COMMAND().
 */
function drush_yamlform_export($yamlform_id = NULL) {
  if (!$yamlform_id) {
    $yamlforms = array_keys(YamlForm::loadMultiple());
    $choices = array_combine($yamlforms, $yamlforms);
    $yamlform_id = drush_choice($choices, dt("Choose a YAML form to export submissions from."));
    if ($yamlform_id === FALSE) {
      return drush_user_abort();
    }
  }

  /** @var \Drupal\yamlform\YamlFormSubmissionExporter $exporter */
  $exporter = \Drupal::service('yamlform_submission.exporter');

  // Get command options as export options.
  $export_options = drush_redispatch_get_options();
  // Convert dashes to underscores.
  foreach ($export_options as $key => $value) {
    unset($export_options[$key]);
    $export_options[str_replace('-', '_', $key)] = $value;
  }
  $export_options += $exporter->getDefaultExportOptions();

  $yamlform = YamlForm::load($yamlform_id);
  YamlFormResultsExportController::batchSet($yamlform, $export_options);
  drush_backend_batch_process();

  $file_path = file_directory_temp() . '/' . $yamlform->id() . '.csv';
  drush_print(file_get_contents($file_path));
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
  if (!empty($yamlform_id) && !YamlForm::load($yamlform_id)) {
    return drush_set_error(dt('YAML form @id not recognized.', ['@id' => $yamlform_id]));
  }
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
    $yamlform_id = drush_choice($choices, dt("Choose a YAML form to purge submissions from."));
    if ($yamlform_id === FALSE) {
      return drush_user_abort();
    }
  }

  // Set the YAML form.
  $yamlform = ($yamlform_id == 'all') ? NULL : YamlForm::load($yamlform_id);

  /** @var \Drupal\yamlform\YamlFormSubmissionStorageInterface $submission_storage */
  $submission_storage = \Drupal::entityManager()->getStorage('yamlform_submission');

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
    $form = new YamlFormResultsClearForm($submission_storage);
    $form->batchSet();
    drush_backend_batch_process();
  }
  else {
    $t_args = [
      '@title' => $yamlform->label(),
    ];
    if (!drush_confirm(dt("Are you sure you want to delete all submissions from '@title' form?", $t_args))) {
      return drush_user_abort();
    }
    $form = new YamlFormSubmissionsPurgeForm($submission_storage);
    $form->batchSet($yamlform);
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

/**
 * Implements drush_hook_COMMAND_validate().
 */
function drush_yamlform_generate_validate() {
  // Array of "Callback arguments" and "command line args".
  $params = func_get_args();
  $params[0] = 'yamlform_submission';
  _drush_plugin_validate($params);
}
