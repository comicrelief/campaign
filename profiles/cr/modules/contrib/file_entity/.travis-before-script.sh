#!/bin/bash

set -e $DRUPAL_TI_DEBUG

# Ensure the right Drupal version is installed.
# Note: This function is re-entrant.
drupal_ti_ensure_drupal

# Add needed dependencies.
cd "$DRUPAL_TI_DRUPAL_DIR"

# These variables come from environments/drupal-*.sh
mkdir -p "$DRUPAL_TI_MODULES_PATH"
cd "$DRUPAL_TI_MODULES_PATH"

# Download Pathauto 8.x-1.x
git clone --depth 1 --branch 8.x-1.x https://github.com/md-systems/pathauto.git
git clone --depth 1 --branch 8.x-1.x http://git.drupal.org/project/token.git
git clone --depth 1 --branch 8.x-3.x http://git.drupal.org/project/ctools.git
