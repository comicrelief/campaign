<?php

use Symfony\Component\Yaml\Yaml;

$databases = array();
$config_directories = array();
$settings['install_profile'] = 'cr';
//$settings['allow_authorize_operations'] = FALSE;
//$settings['session_write_interval'] = 180;
//$settings['class_loader_auto_detect'] = TRUE;
//$settings['omit_vary_cookie'] = TRUE;

/**
 * Load environment variables.
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
 * Include the Pantheon-specific settings file.
 *
 * n.b. The settings.pantheon.php file makes some changes
 *      that affect all envrionments that this site
 *      exists in.  Always include this file, even in
 *      a local development environment, to insure that
 *      the site settings remain consistent.
 */

if (getenv('VCAP_SERVICES')) {
  include __DIR__ . "/settings.cf.php";
}
