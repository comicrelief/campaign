#!/bin/bash

set -e $DRUPAL_TI_DEBUG

# Ensure the right Drupal version is installed.
# Note: This function is re-entrant.
drupal_ti_ensure_drupal

# Download dependencies.
mkdir -p "$DRUPAL_TI_DRUPAL_DIR/$DRUPAL_TI_MODULES_PATH"
cd "$DRUPAL_TI_DRUPAL_DIR/$DRUPAL_TI_MODULES_PATH"
git clone --depth 1 --branch 8.x-1.x https://github.com/drupal-media/embed.git
git clone --depth 1 --branch 8.x-1.x http://git.drupal.org/project/composer_manager.git

# Ensure the module is linked into the code base and enabled.
# Note: This function is re-entrant.
drupal_ti_ensure_module_linked

# Remove the gastonjs library before running drupal-rebuild until
# https://www.drupal.org/node/2652142 is fixed.
rm -rf $DRUPAL_TI_DRUPAL_DIR/vendor/jcalderonzumba/gastonjs

# Initialize composer manager.
php "$DRUPAL_TI_DRUPAL_DIR/$DRUPAL_TI_MODULES_PATH/composer_manager/scripts/init.php"
composer drupal-rebuild
composer update -n --verbose

# Enable main module.
drush en -y url_embed
