# Comic Relief Campaign website

A work in progress. See http://confluence.comicrelief.com/display/RND17/Campaign+platform+notes

## How to set this up locally

## FrontEnd set up

###Install npm. https://docs.npmjs.com/

In the theme directory run:

	npm install

###Install bundler. http://bundler.io/

In the theme directory run:

	bundle install

Bundler will install all gems needed for your project.

###Grunt dev / build

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
You can test by running the following to identify PHP and the location of the configuration file

  php --ini

If one isn't present, copy one using the default template supplied with PHP (you might need to sudo):

  cp /etc/php.ini.default /etc/php.ini

In the php.ini file, ensure the 'date.timezone'property is enabled and set with a timezone (you can find list of values from http://php.net/manual/en/timezones.php):

  date.timezone = Europe/London

### Install and configure Drush 8

Drush 8 is required for Drupal 8. Install instructions can be found [here](http://x-team.com/2015/02/install-drush-8-drupal-8-without-throwing-away-drush-6-7/).

Or use `composer global require drush/drush` and you'll use the last version.
If you want to use drush 6 again `composer global require drush/drush 6.*`


### Install Phing

You first will need to install to install [Phing](www.phing.info), which is a PHP build tool that automates tasks such as re-installing the site, running migrate procedures, tests etc.

Download Phing from http://www.phing.info/trac/wiki/Users/Download. You can install this using

- composer, `composer global require phing/phing` [See installation guide](https://coderwall.com/p/ma_cuq/using-composer-to-manage-global-packages)
- PEAR, follow instructions at http://www.phing.info/trac/wiki/Users/Download

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

## How to run this on Pantheon

Add Pantheon as a second remote

	git remote add pantheon ssh://codeserver.dev.f9291f1f-3819-4964-9c5b-c9f7d5500d28@codeserver.dev.f9291f1f-3819-4964-9c5b-c9f7d5500d28.drush.in:2222/~/repository.git
	
Now you can push to Pantheon to deploy this

https://dashboard.pantheon.io/sites/f9291f1f-3819-4964-9c5b-c9f7d5500d28#dev/code

How to deal with [settings.php on Pantheon](https://pantheon.io/docs/articles/drupal/configuring-settings-php/)

## Skip Travis CI builds

Include the following within any commit message `[ci skip]`

# Debug info

## Ubuntu grunt:build error
```
Property ${app.profile.theme} => profiles/cr/themes/custom/campaign_base
     [exec] Executing command: grunt build
/usr/bin/env: node: No such file or directory
```
Fix: `sudo ln -fs /usr/bin/nodejs /usr/local/bin/node`

Note: Nodejs location may differ.

## Rules of the Road

This section intends to lay down some guiding principles and best-practice approaches for the campaign build. Additions should be submitted as a PR for team discussion.

### Node-based

Everything should be a node. This falls more inline with how Drupal __wants__ things to be done. This approach will also help with Solr indexing implementations later on.

### Susy grid

The grid system for the theme should be generated with the Susy mixins - http://susy.oddbird.net/. So no more Bootstrap grid.

### Theme inheritance

To be discussed.

### Only essential markup

Drupal has a long history of generating bloated markup. For performance reasons, efforts should me made to keep all module generated markup to a minimum.

### Component-based sass
SASS should be written with a component approach in mind, with a view to being able to easily extend a given component. BEM has worked well in the past and probably should be adopted for the campaign theme SASS.

http://alwaystwisted.com/articles/2014-02-27-even-easier-bem-ing-with-sass-33

### Feature provides default content
When writing a custom module - say a Blog article with a node type, fields, views etc. - default content should be provided in code so the feature (don't confuse with the drupal feature module!) as a whole can be developer-reviewed and QA'd as it moves upstream. Default content should be exported as part of `cr_default_content`. 

See https://www.drupal.org/project/default_content (already included in the CR profile).

### Contrib projects
Because of the high-performance nature of the campaign sites, the use of contirb projects should be carefully considered. Adding a contrib module because it gets the job done can sometimes cause issues further down the line.

### Behat tests, where useful
Bear in mind that testing for the existence of certain links on certain pages may not be sustainable, due to ever-changing content. But Behat tests should still be written and provided in your feature where it makes sense to.

http://docs.behat.org/en/v3.0/
https://knpuniversity.com/screencast/behat/using-behat

### Theme JS in footer
As far as I know, Drupal 8 adds JS to the footer by default but it's good one to keep an eye on.

https://www.drupal.org/theme-guide/8/assets

### Everything should be within the profile

https://www.drupal.org/node/2210443

### Views view modes
Views should always use view modes, never field-based views for example.

https://drupalize.me/blog/201403/exploring-new-drupal-8-display-modes

### Document module fields
Modules defining an entity type should document their fields.

See https://github.com/comicrelief/campaign/tree/develop/profiles/cr/modules/custom/cr_article
