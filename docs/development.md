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

### Docker

Docker can be used for local development. To run on your local machine you will need to execute the following from the root directory of the repository,

```bash
docker-compose up -d
```

#### On Mac

The read and write access for mounted volumes is terrible for docker on mac. Because of that the easiest solution is to use docker-sync-unison. You will need homebrew installed in order to follow these instructions.

The first step is to execute the following two commands to install docker sync and fswatch,

```bash
gem install docker-sync
brew install fswatch
```

You will then need to execute the following command in order to run docker-sync, this will need to be running in the background every time you wish to use docker.

```bash
docker-sync start
```
Now rather than running docker up, you will need to run the following command to up docker,

```bash
docker-compose -f docker-compose.yml -f docker-compose.mac.yml up -d
```

### IE9 CSS Issue

IE 9 CSS limitation by preventing more than 4095 selectors in a CSS file.

To fix this issue we have used [Bless](http://blesscss.com/) to split our styles.css into files with less than 4095 selectors and committed them. We have created a condition in our html twig template to add those files only if lte IE9.

Currently our IE9 traffic is less than 1% . This is temporary fix and eventually at some point we will stop supporting IE9 and remove those conditions and files.
