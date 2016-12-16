#!/bin/bash
set -e
# Generate build.properties file on the fly
printf 'drush.bin = ~/.composer/vendor/bin/drush.php\n' > build.properties
printf 'db.querystring='$DB_URL >> build.properties
# Output confirmation
echo 'File: build.properties has been created.'
# Get build dependencies
phing build:prepare
