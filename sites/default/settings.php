<?php

use Symfony\Component\Yaml\Yaml;

$databases = [];
$config_directories = [];
$settings['install_profile'] = 'cr';

/**
 * Load environment variables.
 * Required for CRAFT.
 */
$environment = __DIR__ . "/environment.yml";
if (file_exists($environment)) {
  $environment_variables = Yaml::parse(file_get_contents($environment));

  $databases = $environment_variables['databases'];
  $settings = array_merge($settings, $environment_variables['settings']);
  $config = array_merge($config, $environment_variables['config']);
  $config_directories['sync'] = $environment_variables['config_dir'];
}

/**
 * Include settings for platform.sh
 */
// Automatic Platform.sh settings.
if (file_exists(__DIR__ . '/settings.platformsh.php')) {
  include __DIR__ . '/settings.platformsh.php';
}
// Local settings. These come last so that they can override anything.
// Also used by platform.sh - not for local development!
if (file_exists(__DIR__ . '/settings.local.php')) {
  include __DIR__ . '/settings.local.php';
  $settings['update_free_access'] = FALSE;
  $config_directories[CONFIG_SYNC_DIRECTORY] = 'sites/default/config';
}
