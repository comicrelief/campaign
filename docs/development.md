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

We now add these snippets to the `cr_article.info.yml` file, like

	config_devel:
  	  - node.type.article
  	  - pathauto.pattern.article
  	  - views.view.articles

And we then export these to code, using

	> drush cde cr_article

You can now check the updated `.yml` files in `cr_article/config/install`, and commit these to git.

When you want to do the opposite action (e.g. the `features-revert` functionality), you can use

	> drush cdi cr_article

to import everything from `cr_article/config/install` into the database.

#### Managing default content

Default content can be exported as part of the `cr_default_content` module.

To export content, you need to create a node, taxonomy term, or generally any type of entity first via the UI. Once created, grab the entity id and entity type, and run

	> drush dcer node 12 --folder=profiles/cr/modules/custom/cr_default_content/content

This will export the node plus all references (such as images, files, taxonomy terms, etc.) and structure this in the `content` folder of `cr_default_content`.

You will need to remove the automatically exported user as this generates problems for the installation.

	> rm -fr profiles/cr/modules/custom/cr_default_content/content/user/

This sometimes creates problems with serial identifiers (e.g. file id 7), as they might have been reused from previous installations. The site install will fail but the problem is easily solved by changing

	"self": {
		"href": "http:\/\/default\/file\/7?_format=hal_json"
	},

to

	"self": {
		"href": "http:\/\/default\/file\/7777?_format=hal_json"
	},


### Using Phing

#### Managing configuration

Run
	
	phing config:export
	
to export all config in one go.

#### Managing default content

To update one node at a time, run

	phing content:export:save -Dtype=node -Did=69

If you want to update the content or you added the uuid manually in `cr_default_content.info.yml`, you only have to run

	phing content:export
	
### Front End
	
We use [KSS](https://github.com/kss-node/kss/blob/spec/SPEC.md) to build our styleguide.
	
When you create a new sass component please follow the same pattern from existem files for `grunt watch` to auto generate and update the styleguide with the new component.
