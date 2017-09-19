#!/bin/bash
set -e
# @file
# Run test suite
cd web
drush pm-uninstall cookieconsent toolbar -y
cd ..
# Run behat tests
vendor/bin/behat -n
vendor/bin/behat -ns rest

true
