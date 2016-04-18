#!/bin/bash
# Simple script to check code quality.

set -e $DRUPAL_TI_DEBUG

drush wd-show --severity=warning
if [ $? -ne 0 ]
  then
    exit 1
fi
