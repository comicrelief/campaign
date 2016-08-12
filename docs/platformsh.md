## Platform.sh

We use [platform.sh](http://platform.sh) for deploying Pull Requests to environments.

* Every time you create a new Pull Request, a new environment will be created and the Campaign profile will be installed *from scratch*
* When you keep pushing to that branch, the site won't be reinstalled again. You can still manually reinstall the site via the given Drush integration.

### Command-line access

Platform.sh has an excellent CLI. [Read installation instructions](https://docs.platform.sh/drupal/guides/prerequisites/platform-cli.html). You can now run e.g.

	platform list
	
Important: always run these commands from within your branch. Platform.sh is smart enough to connect this branch to your environment.
	
To tail logs, run

	platform logs deploy --tail
	platform logs cron --tail
	platform logs php --tail

To run drush on the remote environment, e.g. to enable the `devel` module

	platform drush 'en devel'
	
To get the URL of an environment, run

	platform url

