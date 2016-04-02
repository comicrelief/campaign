#!/bin/bash
# Generate build.properties file on the fly
printf 'drush.bin = ~/.composer/vendor/bin/drush.php\n' > build.properties
# Output confirmation
echo 'File: build.properties has been created.'
cp -R . ../"$DRUPAL_TI_DRUPAL_DIR"
cd ../"$DRUPAL_TI_DRUPAL_DIR"
