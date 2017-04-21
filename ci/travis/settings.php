<?php
/**
 * @file
 * Platform.sh example settings.php file for Drupal 8.
 */

// Default Drupal 8 settings.
//
// These are already explained with detailed comments in Drupal's
// default.settings.php file.
//
// See https://api.drupal.org/api/drupal/sites!default!default.settings.php/8
$databases = [];
$config_directories[CONFIG_SYNC_DIRECTORY] = 'sites/default/config';
$settings['update_free_access'] = false;
$settings['container_yamls'][] = __DIR__ . '/services.yml';
$settings['file_scan_ignore_directories'] = [
    'node_modules',
    'bower_components',
];

// The hash_salt should be a unique random value for each application.
// If left unset, the settings.platformsh.php file will attempt to provide one.
// You can also provide a specific value here if you prefer and it will be used
// instead. In most cases it's best to leave this blank on Platform.sh. You
// can configure a separate hash_salt in your settings.local.php file for
// local development.
$settings['hash_salt'] = '7BbC-OM6nFz5BDRB9ksza__3PmJSrcZ-eHY1InKGUlt2cnXFsI6eAJzBuzaABWdzgs_ZLZY3bg';

// Set up a config sync directory.
//
// This is defined inside the read-only "config" directory, deployed via Git.
$settings['install_profile'] = 'cr';
$settings['twig_tweak_enable_php_filter'] = true;

$databases['default']['default'] = array (
    'database' => 'drupal',
    'username' => 'root',
    'password' => '',
    'prefix' => '',
    'host' => '127.0.0.1',
    'port' => '3306',
    'namespace' => 'Drupal\\Core\\Database\\Driver\\mysql',
    'driver' => 'mysql',
);

