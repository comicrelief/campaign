#!/bin/bash
# Simple script to check code quality.

CODE_PATH="profiles/cr/modules/custom"
STANDARD="--standard=DrupalPractice"
EXTENSIONS="--extensions=php,module,inc,install,test,profile,theme"

cd "$DRUPAL_TI_BEHAT_DIR"

vendor/bin/phpcs --config-set installed_paths "vendor/drupal/coder/coder_sniffer"

cd "$TRAVIS_BUILD_DIR"
vendor/bin/phpcs $STANDARD $EXTENSIONS "$CODE_PATH" --runtime-set ignore_warnings_on_exit 1
vendor/bin/phpmd "$CODE_PATH" text codesize,unusedcode,naming
vendor/bin/phpcpd "$CODE_PATH"
