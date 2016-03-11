#!/bin/bash
# Simple script to check code quality.

CODE_PATH="profiles/cr/modules/custom"
STANDARD="--standard=DrupalPractice"
EXTENSIONS="--extensions=php,module,inc,install,test,profile,theme"

$DRUPAL_TI_BEHAT_DIR/vendor/bin/phpcs --config-set installed_paths "$DRUPAL_TI_BEHAT_DIR/vendor/drupal/coder/coder_sniffer"
$DRUPAL_TI_BEHAT_DIR/vendor/bin/phpcs $STANDARD $EXTENSIONS "$CODE_PATH" --runtime-set ignore_warnings_on_exit 1
$DRUPAL_TI_BEHAT_DIR/vendor/bin/phpmd "$CODE_PATH" text codesize,unusedcode,naming
$DRUPAL_TI_BEHAT_DIR/vendor/bin/phpcpd "$CODE_PATH"
