#!/bin/bash
# Simple script to check code quality.

echo $(pwd)

profiles/cr/tests/behat/vendor/bin/phpmd profiles/cr/modules/custom text codesize,unusedcode,naming
