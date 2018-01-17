#!/bin/bash
set -e
# @file
# Run test suite
cd web
drush pmu cookieconsent admin_toolbar_tools -y
drush pmu admin_toolbar -y
drush pmu toolbar -y
cd ..
# Run behat tests
vendor/bin/behat -n
vendor/bin/behat -ns rest

true
