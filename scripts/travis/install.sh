#!/bin/bash
set -e
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
mysql -e "create database $DB"

# Remove default settings so we can re-install fine, this
# is custom logic since we version settings.php in the git repo
rm -fr sites/default/settings.php

# Install the site
phing build
drush use $(pwd)#default
