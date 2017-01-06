## Installation and setup

### Table of Contents

1. [Project Requirements](install.md#requirements)
  - [PHP Configurations](install.md#php-configurations)
  - [Drush v8](install.md#drush-v8)
  - [Phing](install.md#phing)
  - [Bundler](install.md#install-bundler)
2. [Installation](install.md#installation)
  - [Environment.yml](install.md#environmentyml)
  - [Application Requirements](install.md#application-requirements)
  - [Using phing](install.md#using-phing-to-run-the-installation)
  - [Grunt](install.md#grunt)
3. [Ready](install.md#ready)

**Note:** Browse the [troubleshooting guide](troubleshooting.md) if you encounter any problems.

### Requirements

The minimum software requirements to run Drupal 8 can be found at: [https://www.drupal.org/requirements](https://www.drupal.org/requirements)


The other essentials needed to run this solution are:
- drush latest 8.*
- phing 2.15
- bundler

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

### Phing

You first will need to install to install [Phing](www.phing.info), which is a PHP build tool that automates tasks such as re-installing the site, running migrate procedures, tests etc.

Download Phing from [http://www.phing.info/trac/wiki/Users/Download](http://www.phing.info/trac/wiki/Users/Download). 

You can install this using `composer`. [See also installation guide](https://coderwall.com/p/ma_cuq/using-composer-to-manage-global-packages):

```bash
composer global require phing/phing:2.15
```

Alternatively you can install using `PEAR`, follow instructions at [http://www.phing.info/trac/wiki/Users/Download](http://www.phing.info/trac/wiki/Users/Download).

#### Configure Phing

Create your local `build.properties` file by copying the example template in the solution:

```bash
cp build.example.properties build.properties
```

And now adapt `build.properties` adding in your Drush 8 binary location, database connection details, and your local website URL.


### Install Bundler

```bash
gem install bundler
```

## Installation
### Environment.yml
This file has been designed to replace the need for a ```settings.local.php```, this is also how the CRAFT environments gather various environment specific settings and credentials. 

```bash
cp sites/default/example.environment.yml sites/default/environment.yml
```

Change database details and local config/ settings accordingly.

### Using Phing to prepare the application environment

To prepare your local site directories and install dependencies, run:

```bash
phing build:prepare
```

This will create and set up the applications webroot at: `project_root/web` and download; Drupal core, Drupal contrib modules (based on the CR profile makefile) and all application dependencies.
You will need to set your local webserver's document root to `/web`

### Using Phing to run the installation

To (re)install the project from scratch, run:
(This will drop the currect database if there is one and all data will be lost)

```bash
phing build
```

A fresh version of the CR Drupal profile will be installed, this includes a new files directory with regenerated images and default content provided by CR Default content. Grunt will then use composer to compile the SASS into CSS automatically and the Drupal cache will be cleared.

Always run this command everytime you have checked out a branch to ensure you have the latest content and configuration.

During development, 

*Note:* If you see the following exception on `phing build`:

```bash
Exception 'Symfony\Component\DependencyInjection\Exception\InvalidArgumentException' with message 'The service definition "renderer" does not exist.`
```

...change the host value in environment.yml from `host: localhost` to `host: 127.0.0.1`.


To login to the site, you can sign in with the creditentials, username: `admin` and password `admin`. Alternatively to sign-in resetting the password run:

```bash
phing login
```

To remake all contrib modules (for example, when adding a new module), run

```bash
phing make
```

To list all possible Phing targets, check

```bash
phing -l
```

All node modules and gems required for this project will be installed.

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
