#!/bin/bash
set -e
# Start webserver/ driver
cd web/
phantomjs --webdriver=4444 > /dev/null &
sleep 5

true
