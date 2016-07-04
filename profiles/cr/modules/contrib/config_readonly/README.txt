

This module attempts to block all Drupal configuration changes.

The main use case is to lock configuration on a prodcution site and import
config using drush that has been validated on a testing copy of the site.

To set a site in read-only mode add this to setting.php:

    $settings['config_readonly'] = TRUE;

To lock production and not other environments, your code in settings.php
might be a conditional on an environment variable like:


    if (isset($_ENV['AH_SITE_ENVIRONMENT']) && $_ENV['AH_SITE_ENVIRONMENT'] === 'prod') {
      $settings['config_readonly'] = TRUE;
    }
