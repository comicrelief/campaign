#!/bin/bash
set -e
# Start webserver/ driver
cd web/
drush runserver --default-server=builtin 8080 &>/dev/null &
phantomjs --webdriver=4444 > /dev/null &
sleep 5
