#!/bin/bash
# Override the default Drupal TI drupal dir.
DRUPAL_TI_DRUPAL_DIR="$TRAVIS_BUILD_DIR"
# Create database and install Drupal.
mysql -e "create database $DRUPAL_TI_DB"
# Generate build.properties file on the fly
printf 'drush.bin = ~/.composer/vendor/bin/drush.php\n' > build.properties
printf 'db.querystring='$DRUPAL_TI_DB_URL >> build.properties
# Output confirmation
echo 'File: build.properties has been created.'

# Install local grunt
cd "$DRUPAL_TI_DRUPAL_DIR"/"$DRUPAL_TI_THEME_DIR"
npm install grunt --save-dev
bundle install
