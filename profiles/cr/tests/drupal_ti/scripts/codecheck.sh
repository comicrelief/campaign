#!/bin/bash
# Simple script to check code quality.

VENDOR_PATH="profiles/cr/tests/behat/vendor"
CODE_PATH="profiles/cr/modules/custom"

$VENDOR_PATH/bin/phpmd "$CODE_PATH" text codesize,unusedcode,naming
