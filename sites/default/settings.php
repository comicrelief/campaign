<?php
use Symfony\Component\Yaml\Yaml;

$settings['install_profile'] = 'rnd17';
$settings['skip_permissions_hardening'] = TRUE;

$settings['profile_directories'] = ['profiles/cr', 'profiles/rnd17'];
$config_directories['sync'] = 'sites/default/files/config_y8vJ4brpwPsHm-K_yfMf8LPe_JvB6RE6pUM3SJj69h3MNkcxqaw2h7B9E5mc9geVpK0oz61QrA/sync';

/**
 * Load environment variables.
 */
$environment = __DIR__ . "/environment.yml";
if (file_exists($environment)) {
	$environment_variables = Yaml::parse(file_get_contents($environment));

	$databases = $environment_variables['databases'];
	$settings = array_merge($settings, $environment_variables['settings']);
	$config = array_merge($config, $environment_variables['config']);
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
} else {
    include __DIR__ . "/settings.pantheon.php";
}

/**
 * If there is a local settings file, then include it
 */
$local_settings = __DIR__ . "/settings.local.php";
if (file_exists($local_settings)) {
  include $local_settings;
}
