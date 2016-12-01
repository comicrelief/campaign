<?php

use Symfony\Component\Yaml\Yaml;

$databases = [];
$config_directories = [];
$settings['install_profile'] = 'cr';

/**
 * Enable twig php filters
 */
$settings['twig_tweak_enable_php_filter'] = TRUE;

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
 * Load services definition file.
 */
$settings['container_yamls'][] = __DIR__ . '/services.yml';

/**
 * Include settings for platform.sh
 */
if (file_exists(__DIR__ . '/settings.local.php')) {
  // Automatic Platform.sh settings.
  if (file_exists(__DIR__ . '/settings.platformsh.php')) {
    include __DIR__ . '/settings.platformsh.php';
  }

  include __DIR__ . '/settings.local.php';

  // Some specific platform.sh settings
  $settings['update_free_access'] = FALSE;
  $config_directories[CONFIG_SYNC_DIRECTORY] = 'sites/default/config';
}
