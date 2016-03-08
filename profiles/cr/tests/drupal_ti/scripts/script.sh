#!/bin/bash
# @file
# Behat integration - Script step.

set -e $DRUPAL_TI_DEBUG

# Ensure we are in the right directory, we need to overwrite this here
# since it is different from Drupal TI's default setup
DRUPAL_TI_DRUPAL_DIR="$TRAVIS_BUILD_DIR"

# Now go to the local behat tests, being within the project installation is
# needed for example for the drush runner.
cd "$DRUPAL_TI_BEHAT_DIR"

# We need to create a behat.yml file from behat.yml.dist.
drupal_ti_replace_behat_vars

# And run the tests.
ARGS=( $DRUPAL_TI_BEHAT_ARGS )
./vendor/bin/behat "${ARGS[@]}"
phpcs --config-set installed_paths ~/.composer/vendor/drupal/coder/coder_sniffer
phpcs --standard=DrupalPractice --extensions=php,module,inc,install,test,profile,theme modules/custom themes/custom
phpmd modules/custom text codesize,unusedcode,naming
