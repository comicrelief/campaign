<?php

$databases = [];
$config_directories = [];
$settings['install_profile'] = 'cr';
$settings['update_free_access'] = FALSE;
$settings['skip_permissions_hardening'] = TRUE;
$settings['file_scan_ignore_directories'] = [
  'node_modules',
  'bower_components',
];
$settings['entity_update_batch_size'] = 50;
$config_directories[CONFIG_SYNC_DIRECTORY] = 'sites/default/config';
$settings['container_yamls'][] = __DIR__ . '/services.yml';

// The hash_salt should be a unique random value for each application.
// If left unset, the settings.platformsh.php file will attempt to provide one.
// You can also provide a specific value here if you prefer and it will be used
// instead. In most cases it's best to leave this blank on Platform.sh. You
// can configure a separate hash_salt in your settings.local.php file for
// local development.
$settings['hash_salt'] = '7BbC-OM6nFz5BDRB9ksza__3PmJSrcZ-eHY1InKGUlt2cnXFsI6eAJzBuzaABWdzgs_ZLZY3bg';

//
$settings['data_ingestion_endpoint'] = 'https://ingest.data.comicrelief.com/';

// Set up a config sync directory.
//
// This is defined inside the read-only "config" directory, deployed via Git.
$settings['twig_tweak_enable_php_filter'] = true;

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

// Automatic Platform.sh settings.
if (file_exists(__DIR__  . '/settings.platformsh.php')) {
    $databases = [];
    include __DIR__  . '/settings.platformsh.php';
}
