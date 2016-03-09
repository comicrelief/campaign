#!/bin/bash
# @file
# Code check integration - Script step.

cd "$DRUPAL_TI_BEHAT_DIR"
cd "vendor"

bin/phpcs --config-set "installed_paths" ${CS_PROFILE}
bin/phpcs --standard="${CS_STAND}" --extensions=${CS_EXT} ${CODE_BUILD_DIR} --runtime-set "ignore_warnings_on_exit 1"
bin/phpmd ${CODE_BUILD_DIR} "text codesize,unusedcode,naming"
bin/phpcpd ${CODE_BUILD_DIR}

cd "$TRAVIS_BUILD_DIR"
