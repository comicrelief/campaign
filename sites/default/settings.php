<?php

// use Symfony\Component\Yaml\Yaml;

$databases = [];
$config_directories = [];
$settings['update_free_access'] = FALSE;
$settings['container_yamls'][] = __DIR__ . '/services.yml';

/**
 * Load environment variables.
 */
// $environment = __DIR__ . "/environment.yml";
// if (file_exists($environment)) {
//   $environment_variables = Yaml::parse(file_get_contents($environment));

//   $databases = $environment_variables['databases'];
//   $settings = array_merge($settings, $environment_variables['settings']);
//   $config = array_merge($config, $environment_variables['config']);
//   $config_directories['sync'] = $environment_variables['config_dir'];
// }

/**
 * Load services definition file.
 */
$settings['container_yamls'][] = __DIR__ . '/services.yml';

// Set up a config sync directory.
//
// This is defined inside the read-only "config" directory. This works well,
// however it requires a patch from issue https://www.drupal.org/node/2607352
// to fix the requirements check and the installer.
$config_directories[CONFIG_SYNC_DIRECTORY] = '../sync';

$settings['install_profile'] = 'cr';

/**
 * Include settings for platform.sh
 */
// Automatic Platform.sh settings.
if (file_exists(__DIR__ . '/settings.platformsh.php')) {
  include __DIR__ . '/settings.platformsh.php';
  $config_directories[CONFIG_SYNC_DIRECTORY] = 'sites/default/config';
}
// Local settings. These come last so that they can override anything.
if (file_exists(__DIR__ . '/settings.local.php')) {
  include __DIR__ . '/settings.local.php';
}
