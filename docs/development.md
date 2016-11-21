## Development instructions

### Manually

#### Managing configuration

All custom code should be part of the `cr` profile, and more specifically as part of the profile itself, either as custom modules (previously `features`) in `profiles/cr/modules/custom`, either as part of the theme in `profiles/cr/themes/`.

Configuration management is handled using the contrib module [`config_devel`](http://drupal.org/project/config_devel), which is a "light-weight" version of what we were using `features` for.

A typical workflow goes like

	> drush cli | grep article
	node.type.article
	pathauto.pattern.article
	views.view.articles
	...

to find the relevant configuration snippets that need to be exported, in this case, related to the "article" content type. You can also the GUI at `admin/config/development/configuration/single/export` to find these configuration files.

Warning: if you export fields, make sure to list the string `field.storage.*` **before** `field.field.*` in the `*.info.yml` file.

We now add these snippets to the `cr_article.info.yml` file, like

	config_devel:
  	  - node.type.article
  	  - pathauto.pattern.article
  	  - views.view.articles

And we then export these to code, using

	> drush cde cr_article

On running this command if you get the following error:

	> Command cdi needs the following extension(s) enabled to run: config_devel.
	The drush command 'cdi cr_article' could not be executed.

Then clear your cache by running the below command:

	> drush cr

You can now check the updated `.yml` files in `cr_article/config/install`, and commit these to git.

When you want to do the opposite action (e.g. the `features-revert` functionality), you can use

	> drush cdi cr_article

to import everything from `cr_article/config/install` into the database.

#### Managing default content

See [Exporting default content](default-content.md) for a way to export content using Drush and using Phing.

### Using Phing

#### Managing configuration

Run

	phing config:export

to export all config in one go.

**Warning: you still need to add the config names to *.info.yml if not they won't be exported!**

### Front End

#### Grunt Tasks

##### build

`phing grunt:build`

##### watch

`phing grunt:watch`

We use [KSS](https://github.com/kss-node/kss/blob/spec/SPEC.md) to build our styleguide.

When you create a new sass component please follow the same pattern from existem files for `grunt watch` to auto generate and update the styleguide with the new component.

### IE9 CSS Issue

IE 9 CSS limitation by preventing more than 4095 selectors in a CSS file.

To fix this issue we have used [Bless](http://blesscss.com/) to split our styles.css into files with less than 4095 selectors and committed them. We have created a condition in our html twig template to add those files only if lte IE9.

Currently our IE9 traffic is less than 1% . This is temporary fix and eventually at some point we will stop supporting IE9 and remove those conditions and files.
