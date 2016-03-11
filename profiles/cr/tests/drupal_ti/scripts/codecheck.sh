#!/bin/bash
# Simple script to check code quality.

VENDOR_PATH="profiles/cr/tests/behat/vendor"
CODE_PATH="profiles/cr/modules/custom"
STANDARD="--standard=DrupalPractice"
EXTENSIONS="--extensions=php,module,inc,install,test,profile,theme"
echo $($DRUPAL_TI_BEHAT_DIR)

$VENDOR_PATH/bin/phpcs --config-set installed_paths "$VENDOR_PATH/drupal/coder/coder_sniffer"
$VENDOR_PATH/bin/phpcs $STANDARD $EXTENSIONS "$CODE_PATH" --runtime-set ignore_warnings_on_exit 1
$VENDOR_PATH/bin/phpmd "$CODE_PATH" text codesize,unusedcode,naming
$VENDOR_PATH/bin/phpcpd "$CODE_PATH"
