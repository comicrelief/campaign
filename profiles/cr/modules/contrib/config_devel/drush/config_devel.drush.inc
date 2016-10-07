<?php

/**
 * @file
 * Configuration development module drush integration.
 */

use Drupal\Core\Config\InstallStorage;

/**
 * Implements hook_drush_command().
 */
function config_devel_drush_command() {
  $items = array();

  $description = '';
  $description .= "Write back configuration to module's config/install directory." . "\n";
  $description .= "List which configuration settings you want to export in the" . "\n";
  $description .= "module's info file by listing them under 'config_devel', as shown below:"  . "\n";
  $description .= "\n";
  $description .= "config_devel:"  . "\n";
  $description .= "  - entity.view_display.node.article.default"  . "\n";
  $description .= "  - entity.view_display.node.article.teaser"  . "\n";
  $description .= "  - field.instance.node.article.body"  . "\n";

  $items['config-devel-export'] = array(
    'description' => $description,
    'arguments' => array(
      'module' => 'Module machine name.',
    ),
    'options' => array(
    ),
    'required-arguments' => TRUE,
    'examples' => array(
      'drush config-devel-export MODULE_NAME' => 'Write back configuration to the specified module, based on .info file.',
    ),
    'aliases' => array('cde', 'cd-em'),
  );

  $items['config-devel-import'] = array(
    'description' => $description,
    'arguments' => array(
      'module' => 'Module machine name.',
    ),
    'options' => array(
    ),
    'required-arguments' => TRUE,
    'examples' => array(
      'drush config-devel-import MODULE_NAME' => 'Import configuration from the specified module into the active storage, based on .info file.',
    ),
    'aliases' => array('cdi', 'cd-im'),
  );

  $items['config-devel-import-one'] = array(
    'description' => $description,
    'arguments' => array(
      'path' => 'Config file name.',
    ),
    'options' => array(
    ),
    'required-arguments' => TRUE,
    'examples' => array(
      'drush config-devel-import-one system.site.yml' => 'Import the contents of system.site.yml into the config object system.site.',
      'drush config-devel-import-one system.site' => 'Import the standard input into the config object system.site. Helpful for scripting copying to remote',
    ),
    'aliases' => array('cdi1', 'cd-i1'),
  );

  $items['config-devel-diff'] = [
    'description' => $description,
    'arguments' => [
      'module' => 'Module machine name.',
    ],
    'options' => [],
    'required-arguments' => true,
    'examples' => [
      'drush panoli MODULE_NAME' => 'Check the differences between the .info file & the configs folders.',
    ],
    'aliases' => ['cdd'],
  ];

  return $items;
}

/**
 * Drush command callback.
 */
function drush_config_devel_export($extension) {
  // Determine the type of extension we're dealing with.
  $type = drush_config_devel_get_type($extension);

  if ($type) {
    // Get the config
    $config = drush_config_devel_get_config($type, $extension);

    // Process config
    if (isset($config['install'])) {
      drush_config_devel_process_config($config['install'], $type, $extension, InstallStorage::CONFIG_INSTALL_DIRECTORY);
    }

    // If we have any optional configuration, process that as well.
    if (isset($config['optional'])) {
      drush_config_devel_process_config($config['optional'], $type, $extension, InstallStorage::CONFIG_INSTALL_DIRECTORY);
    }
  }
  else {
    drush_set_error("Couldn't export configuration. The '$extension' extension is not enabled.");
  }
}

/**
 * Drush command callback.
 */
function drush_config_devel_import($extension) {
  // Determine the type of extension we're dealing with.
  $type = drush_config_devel_get_type($extension);

  if ($type) {
    // Get the config
    $config = drush_config_devel_get_config($type, $extension);

    // Import config
    if (isset($config['install'])) {
      drush_config_devel_import_config($config['install'], $type, $extension, InstallStorage::CONFIG_INSTALL_DIRECTORY);
    }

    // Import optional config
    if (isset($config['optional'])) {
      drush_config_devel_import_config($config['optional'], $type, $extension, InstallStorage::CONFIG_INSTALL_DIRECTORY);
    }
  }
  else {
    drush_set_error("Couldn't import configuration. The '$extension' extension is not enabled.");
  }
}

/**
 * Drush command callback.
 */
function drush_config_devel_import_one($path) {
  $contents = '';
  if (!file_exists($path)) {
    if (substr($path, -4) != '.yml') {
      $contents = file_get_contents('php://stdin');
    }
    elseif (!empty($_SERVER['PWD'])) {
      $path = $_SERVER['PWD'] . '/' . trim($path, '/');
    }
  }
  if ($contents || file_exists($path)) {
    \Drupal::service('config_devel.auto_import_subscriber')->importOne($path, '', $contents);
  }
  else {
    drush_log('file not found', 'error');
    exit;
  }
}

/**
 * Drush command callback.
 */
function drush_config_devel_diff($extension)
{
  $type = drush_config_devel_get_type($extension);

  if ($type) {
    $listdir = [];
    $listconfig = [];
    // Get the config
    $config = drush_config_devel_get_config($type, $extension);
    if (isset($config['install'])) {
      $listconfig = array_merge($listconfig, $config['install']);
    }
    if (isset($config['optional'])) {
      $listconfig = array_merge($listconfig, $config['optional']);
    }
    $scan_dir = array_slice(scandir(
      drupal_get_path($type, $extension) . "/" . InstallStorage::CONFIG_INSTALL_DIRECTORY
    ), 2);
    foreach ($scan_dir as $value) {
      $listdir[] = substr($value, 0, -4);
    }
    if (empty(array_diff($listconfig, $listdir))) {
      drush_print_r("Congrats everything is ok!");
    } else {
      drush_set_error(
        "You missed some files. Add these to the '$extension.info.yml' file or export your new configs."
      );
      drush_print_r(array_diff($listconfig, $listdir));
    }
  } else {
    drush_set_error("Couldn't export configuration. The '$extension' extension is not enabled.");
  }
}


/**
 * Exports a list of configuration entities.
 *
 * @param array $config_list
 *   An array of configuration entities.
 * @param string $type
 *   The type of extension we're exporting, one of module or theme.
 * @param string $extension
 *   The module, theme or install profile we're exporting.
 * @param string $directory
 *   The directory we're exporting to.
 */
function drush_config_devel_process_config($config_list, $type, $extension, $directory) {
  $config_path = drupal_get_path($type, $extension) . "/$directory";
  foreach ($config_list as $name) {
    $config = \Drupal::config($name);
    $file_names = array($config_path . '/' . $name . '.yml');

    \Drupal::service('config_devel.writeback_subscriber')->writeBackConfig($config, $file_names);
  }
}

/**
 * Imports a list of configuration entities
 *
 * @param array $config_list
 *   An array of configuration entities.
 * @param string $type
 *   The type of extension we're exporting, one of module or theme.
 * @param string $extension
 *   The module, theme or install profile we're exporting.
 * @param string $directory
 *   The directory we're exporting to.
 */
function drush_config_devel_import_config($config_list, $type, $extension, $directory) {
  $config_path = drupal_get_path($type, $extension) . "/$directory";
  foreach ($config_list as $name) {
    $file_name = $config_path . '/' . $name . '.yml';
    drush_config_devel_import_one($file_name);
  }
}

/**
 * Gets the config.
 *
 * @param  string $type
 *   module, theme or profile
 * @param  string $extension
 *   extension name
 * @return array
 *   An array containing install and optional config
 */
function drush_config_devel_get_config($type, $extension) {
  $filename = drupal_get_path($type, $extension) . '/' . $extension .'.info.yml';
  $info = \Drupal::service('info_parser')->parse($filename);

  $config = array();
  if (isset($info['config_devel'])) {
    // Keep backwards compatibility for the old format.
    if (!isset($info['config_devel']['install'])) {
      $info['config_devel']['install'] = $info['config_devel'];
    }

    $config['install'] = $info['config_devel']['install'];

    // If we have any optional configuration, fetch that as well.
    if (isset($info['config_devel']['optional'])) {
      $config['optional'] = $info['config_devel']['optional'];
    }
  }

  return $config;
}

/**
 * Gets the type for the given extension.
 *
 * @param  string $extension
 *   extension name
 * @return string
 *   module, theme, profile, or FALSE if no valid extension provided.
 */
function drush_config_devel_get_type($extension) {
  $type = NULL;
  if (\Drupal::moduleHandler()->moduleExists($extension)) {
    $type = 'module';
  }
  elseif (\Drupal::service('theme_handler')->themeExists($extension)) {
    $type = 'theme';
  }
  elseif (drupal_get_profile() === $extension) {
    $type = 'profile';
  }

  return $type;
}
