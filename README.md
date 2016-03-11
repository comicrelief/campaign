# Comic Relief Campaign website

## Contents

- About
- Installation and setup
- Rules of the Road - developing and extending the solution
- More information

## About

This is a quick overview to get the Campaign website running on an environment.

Detailed information can be found at:
[http://confluence.comicrelief.com/x/gwQq](http://confluence.comicrelief.com/x/gwQq)

Other platform notes can be found at (note: a work in progress):
[http://confluence.comicrelief.com/x/iQQq](http://confluence.comicrelief.com/x/iQQq)

## Installation and setup

The essentials are:
- grunt
- drush v8
- phing

### Install Grunt

##### Install npm
In the theme directory run:

	npm install

Source: [https://docs.npmjs.com/](https://docs.npmjs.com/)


##### Install bundler

In the theme directory run:

	bundle install

Bundler will install all gems needed for your project.

Source: [http://bundler.io/](http://bundler.io/)

##### Grunt dev / build

For dev run:

	grunt default

Grunt will watch all SASS / TWIG / JS / Images for changes

And,
- Compile CSS
- jshint JS
- Generate compass image sprites
- Add source sass map to help inspect sass files in browser inspector
- Reload your browser (you need livereload chrome extension)

For prod run:

	grunt build

Grunt will compile CSS, remove comments, remove sass source file, minify and concatenate js.

You can also do this from the root of this repository using

	phing grunt:build

### PHP Configurations
Ensure your environment can run PHP from the command line with a php.ini configuration file loaded in.

Further details can be found at:
[http://confluence.comicrelief.com/x/jwE2](http://confluence.comicrelief.com/x/jwE2)

### Install and configure Drush 8

Drush 8 is required for Drupal 8. Install instructions can be found [here](http://x-team.com/2015/02/install-drush-8-drupal-8-without-throwing-away-drush-6-7/).

Or use `composer global require drush/drush` and you'll use the last version.
If you want to use drush 6 again `composer global require drush/drush 6.*`


### Install Phing

You first will need to install to install [Phing](www.phing.info), which is a PHP build tool that automates tasks such as re-installing the site, running migrate procedures, tests etc.

Download Phing from [http://www.phing.info/trac/wiki/Users/Download](http://www.phing.info/trac/wiki/Users/Download). You can install this using
- composer, `composer global require phing/phing` [See installation guide](https://coderwall.com/p/ma_cuq/using-composer-to-manage-global-packages)
- PEAR, follow instructions at [http://www.phing.info/trac/wiki/Users/Download](http://www.phing.info/trac/wiki/Users/Download)

### Configure Phing

Copy over `build.example.properties` to `build.properties`

	cp build.example.properties build.properties

And now adapt `build.properties` adding in your Drush 8 binary location, database connection details, and your local website URL.

Now, do the same with `settings.local.php`

	cp sites/default/settings.example.local.php sites/default/settings.local.php

And change the database connection details as well.

### Using Phing

To install the site, now run

	phing build

Note: If you see the following exception on `phing build`:

`Exception 'Symfony\Component\DependencyInjection\Exception\InvalidArgumentException' with message 'The service definition "renderer" does not exist.`

...change the host value in settings.local.php from `'host' => 'localhost',` to `'host' => '127.0.0.1',`.

To login to the site, for example run

	phing login

To remake all contrib modules (for example, when adding a new module), run

	phing make

To list all possible Phing targets, check

	phing -l


## Rules of the Road
Details and discussions on ways to developing and extending the solution can be found at:
[http://confluence.comicrelief.com/x/kQE2](http://confluence.comicrelief.com/x/kQE2)

## More information
Other advanced settings and tips on troubleshooting can be found at:
[http://confluence.comicrelief.com/x/mAE2](http://confluence.comicrelief.com/x/mAE2)
