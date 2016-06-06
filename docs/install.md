## Installation and setup

### Table of Contents

1. [Requirements](install.md#requirements)
2. [Grunt](install.md#grunt)
3. [PHP Configurations](install.md#php-configurations)
4. [Drush v8](install.md#drush-v8)
5. [Phing](install.md#phing)

**Note:** Browse the [troubleshooting guide](troubleshooting.md) if you encounter any problems.

### Requirements

The minimum software requirements to run Drupal 8 can be found at: [https://www.drupal.org/requirements](https://www.drupal.org/requirements)


The other essentials needed to run this solution are:
- drush v8
- phing
- grunt

The next steps will cover how to install these tools.

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
composer global require phing/phing
```

Alternatively you can install using `PEAR`, follow instructions at [http://www.phing.info/trac/wiki/Users/Download](http://www.phing.info/trac/wiki/Users/Download).

##### Configure Phing

Create your local `build.properties` file by copying the example template in the solution:

```bash
cp build.example.properties build.properties
```

And now adapt `build.properties` adding in your Drush 8 binary location, database connection details, and your local website URL.

### Grunt

Grunt is simply used to monitor changes in front-end asset files and trigger tasks to build the theme, e.g. compile css, minifying js, etc.

##### Install npm & Bundler

From the root of the repo run:

```bash
phing build:prepare
```

All node modules and gems required for this project will be installed.


##### Running Grunt

```bash
phing grunt:build
```

Grunt will watch all SASS / TWIG / JS / Images assets for changes and will:
- Compile CSS
- jshint JS
- Generate compass image sprites
- Add source sass map to help inspect sass files in browser inspector
- Reload your browser (you need livereload chrome extension)


Grunt will compile CSS, remove comments, remove sass source file, minify and concatenate js.

### Environment.yml
This file has been designed to replace the need for a ```settings.local.php```, this is also how the CRAFT environments gather various environment specific settings and credentials. 

```bash
cp sites/default/example.environment.yml sites/default/environment.yml
```

Change database details accordingly.

##### Using Phing

To prepare the site to develop, run:

```bash
phing build:dev
```

This will setup a fresh files folder directory, install the CR Drupal profile, use grunt to compile the front-end file assets, create a Drupal user and enable any developer modules.

Always run everytime you have checked out a branch.

During development, 

*Note:* If you see the following exception on `phing build:dev`:

```bash
Exception 'Symfony\Component\DependencyInjection\Exception\InvalidArgumentException' with message 'The service definition "renderer" does not exist.`
```

...change the host value in settings.local.php from `'host' => 'localhost',` to `'host' => '127.0.0.1',`.

If you only wish to reload the CR Drupal profile, i.e. Drupal code and rebuild database, then run:

```bash
phing install
```

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

### Ready?

You now have everything to start developing. But before you do have a read of [Rules of the Road](rules_of_the_road.md) to understand and follow some guiding principles and best-practice approaches.
