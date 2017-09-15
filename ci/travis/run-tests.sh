#!/bin/bash
set -e
# @file
# Run test suite
cd web
drush pm-uninstall cookieconsent toolbar -y
cd ..
# Run behat tests
vendor/bin/behat
vendor/bin/behat -s rest

true
