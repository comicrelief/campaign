#!/bin/bash
# Start webserver/ driver
drush runserver --default-server=builtin 8080 &>/dev/null &
phantomjs --webdriver=4444 > /dev/null &
sleep 5
