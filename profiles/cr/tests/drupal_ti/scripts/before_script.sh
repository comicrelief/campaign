#!/bin/bash
# Start webserver/ driver
#drush runserver --default-server=builtin 8080 &>/dev/null &
#phantomjs --webdriver=4444 > /dev/null &
#sleep 5
# Simple script to install drupal for travis-ci running.
# Clear caches and run a web server.
drupal_ti_clear_caches
drupal_ti_run_server
# Start xvfb and selenium.
drupal_ti_ensure_xvfb
drupal_ti_ensure_webdriver
