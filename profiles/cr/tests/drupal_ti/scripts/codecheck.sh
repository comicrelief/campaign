#!/bin/bash
# @file
# Code quality - Script step.

CMDBIN="${$TRAVIS_BUILD_DIR}/profiles/cr/tests/behat/vendor/bin"
DRUPAL_CODER="${$TRAVIS_BUILD_DIR}/profiles/cr/tests/behat/vendor/drupal/coder/coder_sniffer"

"${CMDBIN}/phpcs" --config-set installed_paths "${DRUPAL_CODER}"
"${CMDBIN}/phpcs" --standard=DrupalPractice --extensions=php,module,inc,install,test,profile,theme profiles/cr/modules/custom --runtime-set ignore_warnings_on_exit 1
"${CMDBIN}/phpmd" profiles/cr/modules/custom text codesize,unusedcode,naming
"${CMDBIN}/phpcpd" profiles/cr/modules/custom
