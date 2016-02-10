#!/bin/bash
# Simple script to install drupal for travis-ci running.

set -e $DRUPAL_TI_DEBUG

# Ensure the right Drupal version is installed.
if [ -d "$DRUPAL_TI_DRUPAL_DIR" ]
then
	return
fi

# HHVM env is broken: https://github.com/travis-ci/travis-ci/issues/2523.
PHP_VERSION=`phpenv version-name`
if [ "$PHP_VERSION" = "hhvm" ]
then
	# Create sendmail command, which links to /bin/true for HHVM.
	BIN_DIR="$TRAVIS_BUILD_DIR/../drupal_travis/bin"
	mkdir -p "$BIN_DIR"
	ln -s $(which true) "$BIN_DIR/sendmail"
	export PATH="$BIN_DIR:$PATH"
fi

# Create database and install Drupal.
mysql -e "create database $DRUPAL_TI_DB"

mkdir -p "$DRUPAL_TI_DRUPAL_BASE"

cd $DRUPAL_TI_DRUPAL_BASE
drush make --no-recursion "$TRAVIS_BUILD_DIR/stub.make" "$DRUPAL_TI_DRUPAL_BASE/drupal"
rm -rf drupal/profiles/drupalmel

# Point project into the drupal installation.
ln -sf "$TRAVIS_BUILD_DIR" "drupal/profiles/$DRUPAL_TI_MODULE_NAME"

cd "$TRAVIS_BUILD_DIR"
drush make --no-core --contrib-destination=./ ./drupal-org.make -y

cd "$DRUPAL_TI_DRUPAL_BASE/drupal"

php -d sendmail_path=$(which true) ~/.composer/vendor/bin/drush.php --yes site-install $DRUPAL_TI_MODULE_NAME --db-url="$DRUPAL_TI_DB_URL"
drush use $(pwd)#default

# Clear caches and run a web server.
drupal_ti_clear_caches
drupal_ti_run_server

# Start xvfb and selenium.
drupal_ti_ensure_xvfb
drupal_ti_ensure_webdriver
