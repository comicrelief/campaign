#!/bin/bash
set -e
# @file
# Run test suite
cd web
drush pm-uninstall cookieconsent -y
cd ..
# Check the config match with the info.yml
#phing config:check
# Run behat tests
vendor/bin/behat -p travis
