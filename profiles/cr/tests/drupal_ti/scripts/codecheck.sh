#!/bin/bash
# @file
# Behat integration - Script step.

CMDBIN="profiles/cr/tests/behat/vendor/bin"

"${CMDBIN}/phpcs" --config-set installed_paths "${CMDBIN}/../drupal/coder/coder_sniffer"
"${CMDBIN}/phpcs" --standard=DrupalPractice --extensions=php,module,inc,install,test,profile,theme profiles/cr/modules/custom --runtime-set ignore_warnings_on_exit 1
"${CMDBIN}/phpmd" profiles/cr/modules/custom text codesize,unusedcode,naming
"${CMDBIN}/phpcpd" profiles/cr/modules/custom
