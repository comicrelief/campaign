## Installation

1. [Requirements](install.md#requirements)
2. [Installation](install.md#installation-1)
3. [Ready?](install.md#ready)

**Note:** Browse the [troubleshooting guide](troubleshooting.md) if you encounter any problems.

### Requirements

The minimum software requirements to run Drupal 8 can be found at: [https://www.drupal.org/requirements](https://www.drupal.org/requirements)


The other essentials needed to run this solution are:
- drush latest 8.*
or
- [Docker](https://docs.docker.com/engine/installation/)

After we've configured PHP on your local environment we'll move on to installing these prerequisites.

#### PHP Configurations

Ensure your environment can run PHP from the command line with a php.ini configuration file loaded in. This will be essential for Drush and Phing to work.

You can test by running the following to identify PHP and the location of the configuration file

```bash
php --ini
```

If one isn't present, copy one using the default template supplied with PHP (you might need to sudo):

```bash
cp /etc/php.ini.default /etc/php.ini
```

In the php.ini file, ensure the `date.timezone` property is enabled and set with a timezone

```ini
[Date]
date.timezone = Europe/London
```

#### Drush 8

Drush 8 is required for Drupal 8. Install instructions can be found [here](http://x-team.com/2015/02/install-drush-8-drupal-8-without-throwing-away-drush-6-7/).

**NOTE:** This documentation asks to clone the master branch of the drush repo which will contain a higher version than the one needed (9.x or above). Explore the [Drush GitHub repo](https://github.com/drush-ops/drush) to determine which branch or tag release to checkout.

Alternatively you can use `composer` to install drush:

```bash
composer global require drush/drush 8.*
```

### Installation

#### Composer

To install the site dependencies you have to run
```bash
composer install
```
Once this is done you'll have all your dependencies in place to install your Drupal website.

In order to compile CSS you'll need to execute
```bash
composer grunt:build
```
and install drupal with
```bash
composer drupal:install
```
If you have any issue with the database please check the config of the file `web/sites/default/settings.php`.

Or you if you want to execute all those commands in one go - including some sample content provided by `cr_default_content`, you can run
```bash
composer campaign:build
```

#### Grunt

Grunt is a javascript task runner we've used to kick off various tasks within the build process.

Grunt will watch all SASS, TWIG, JS, images assets for changes and will:
- Compile CSS
- jshint JS
- Generate compass image sprites
- Add source sass map to help inspect sass files in browser inspector
- Reload your browser (you need livereload chrome extension)

Grunt will compile CSS, remove comments, remove sass source file, minify and concatenate js.

You compile all SASS manually at anytime via the command below:

```bash
composer grunt:build
```

## Ready?

You now have everything to start developing. But before you do have a read of [Rules of the Road](rules_of_the_road.md) to understand and follow some guiding principles and best-practice approaches.
