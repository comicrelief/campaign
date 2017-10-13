<?php

$databases = [];
$config_directories = [];
$settings['install_profile'] = 'cr';
$settings['update_free_access'] = FALSE;
$settings['skip_permissions_hardening'] = TRUE;
$settings['entity_update_batch_size'] = 50;
$settings['file_scan_ignore_directories'] = [
  'node_modules',
  'bower_components',
];
$settings['entity_update_batch_size'] = 50;

$databases['default']['default'] = array (
  'database' => 'drupal',
  'username' => 'root',
  'password' => 'root',
  'prefix' => '',
  'host' => 'mysql',
  'port' => '3306',
  'namespace' => 'Drupal\\Core\\Database\\Driver\\mysql',
  'driver' => 'mysql',
);

$config_directories[CONFIG_SYNC_DIRECTORY] = 'sites/default/config';
$settings['hash_salt'] = '';

/**
 * Enable twig php filters
 */
$settings['twig_tweak_enable_php_filter'] = TRUE;

/**
 * Load services definition file.
 */
$settings['container_yamls'][] = __DIR__ . '/services.yml';

/**
 * Include settings for platform.sh
 */
if (file_exists(__DIR__ . '/settings.platformsh.php')) {
  include __DIR__ . '/settings.platformsh.php';
  $settings['update_free_access'] = FALSE;
}
if (file_exists(__DIR__ . '/settings.local.php')) {
  include __DIR__ . '/settings.local.php';
}
