#!/bin/bash
# @file
# Behat integration - Script step.
set -e $DRUPAL_TI_DEBUG

# Now go to the local behat tests, being within the project installation is
# needed for example for the drush runner.
cd "$DRUPAL_TI_BEHAT_DIR"

# We need to create a behat.yml file from behat.yml.dist.
drupal_ti_replace_behat_vars

# And run the tests.
ARGS=( $DRUPAL_TI_BEHAT_ARGS )
./vendor/bin/behat "${ARGS[@]}"
