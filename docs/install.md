## Installation and setup

### Table of Contents

1. [Project Requirements](install.md#requirements)
  - [PHP Configurations](install.md#php-configurations)
  - [Drush v8](install.md#drush-v8)
  - [Bundler](install.md#install-bundler)
2. [Installation](install.md#installation)
  - [Environment.yml](install.md#composer)
  - [Application Requirements](install.md#application-requirements)
  - [Grunt](install.md#grunt)
3. [Ready](install.md#ready)

**Note:** Browse the [troubleshooting guide](troubleshooting.md) if you encounter any problems.

### Requirements

The minimum software requirements to run Drupal 8 can be found at: [https://www.drupal.org/requirements](https://www.drupal.org/requirements)


The other essentials needed to run this solution are:
- drush latest 8.*
- bundler
or
- [Docker](https://docs.docker.com/engine/installation/)

After we've configured PHP on your local environment we'll move on to installing these prerequisites.

### PHP Configurations

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

### Drush 8

Drush 8 is required for Drupal 8. Install instructions can be found [here](http://x-team.com/2015/02/install-drush-8-drupal-8-without-throwing-away-drush-6-7/).

**NOTE:** This documentation asks to clone the master branch of the drush repo which will contain a higher version than the one needed (9.x or above). Explore the [Drush GitHub repo](https://github.com/drush-ops/drush) to determine which branch or tag release to checkout.

Alternatively you can use `composer` to install drush:

```bash
composer global require drush/drush 8.*
```

If you want to use drush 6 or 7 again then simply:

```  
composer global require drush/drush 6.*
composer global require drush/drush 7.*
```

### Install Bundler

```bash
gem install bundler
```

## Installation
### Composer
To install the site dependencies you have to execute
```bash
composer install
```
Once is done you'll have a drupal site ready to use.

In order to compile the css you'll need to execute
```bash
composer grunt:build
```
and after that you are ready to install drupal with
```bash
composer drupal:install
```
If you have any issue with the database please check the config of the file `web/sites/default/settings.php`

### Grunt

Grunt is a javascript task runner we've used to kick off various tasks within the build process, these include but are not limited to;

Grunt will watch all SASS / TWIG / JS / Images assets for changes and will:
- Compile CSS
- jshint JS
- Generate compass image sprites
- Add source sass map to help inspect sass files in browser inspector
- Reload your browser (you need livereload chrome extension)

Grunt will compile CSS, remove comments, remove sass source file, minify and concatenate js.

##### Running Compass compile via Grunt cli

Alternatively you compile all SASS manually at anytime via the command below:

```bash
phing grunt:build
```
## Ready?

You now have everything to start developing. But before you do have a read of [Rules of the Road](rules_of_the_road.md) to understand and follow some guiding principles and best-practice approaches.
