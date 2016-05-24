# Generate build.properties file on the fly
printf 'drush.bin = ~/.composer/vendor/bin/drush.php\n' > build.properties
printf 'db.querystring='$DRUPAL_TI_DB_URL >> build.properties
# Output confirmation
echo 'File: build.properties has been created.'
# Remove gem cache
rm $DRUPAL_TI_DRUPAL_DIR/Gemfile.lock
