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

[http://alwaystwisted.com/articles/2014-02-27-even-easier-bem-ing-with-sass-33](http://alwaystwisted.com/articles/2014-02-27-even-easier-bem-ing-with-sass-33)

### Feature provides default content
When writing a custom module - say a Blog article with a node type, fields, views etc. - default content should be provided in code so the feature (don't confuse with the drupal feature module!) as a whole can be developer-reviewed and QA'd as it moves upstream. Default content should be exported as part of `cr_default_content`.

See [https://www.drupal.org/project/default_content](See https://www.drupal.org/project/default_content) (already included in the CR profile).

### Contrib projects
Because of the high-performance nature of the campaign sites, the use of contirb projects should be carefully considered. Adding a contrib module because it gets the job done can sometimes cause issues further down the line.

### Behat tests, where useful
Bear in mind that testing for the existence of certain links on certain pages may not be sustainable, due to ever-changing content. But Behat tests should still be written and provided in your feature where it makes sense to.

[http://docs.behat.org/en/v3.0/](http://docs.behat.org/en/v3.0/)
[https://knpuniversity.com/screencast/behat/using-behat](https://knpuniversity.com/screencast/behat/using-behat)

### Theme JS in footer
As far as I know, Drupal 8 adds JS to the footer by default but it's good one to keep an eye on.

[https://www.drupal.org/theme-guide/8/assets](https://www.drupal.org/theme-guide/8/assets)

### Everything should be within the profile
Use Drupal's recommended guidelines on how to write a profile:
[https://www.drupal.org/node/2210443](https://www.drupal.org/node/2210443)

### Views view modes
Views should always use view modes, never field-based views for example.

[https://drupalize.me/blog/201403/exploring-new-drupal-8-display-modes](https://drupalize.me/blog/201403/exploring-new-drupal-8-display-modes)

### Document module fields
Modules defining an entity type should document their fields.

See [https://github.com/comicrelief/campaign/tree/develop/profiles/cr/modules/custom/cr_article](https://github.com/comicrelief/campaign/tree/develop/profiles/cr/modules/custom/cr_article)

## More information
Other advanced settings and tips on troubleshooting can be found at:
[http://confluence.comicrelief.com/x/mAE2](http://confluence.comicrelief.com/x/mAE2)
