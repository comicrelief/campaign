## Platform.sh

We use [platform.sh](http://platform.sh) for deploying Pull Requests to environments.

* Every time you create a new Pull Request, a new environment will be created and the Campaign profile will be installed *from scratch*
* When you keep pushing to that branch, the site won't be reinstalled again. You can still manually reinstall the site via the given Drush integration.

### Command-line access

Platform.sh has an excellent CLI. [Read installation instructions](https://docs.platform.sh/drupal/guides/prerequisites/platform-cli.html). You can now run e.g.

	platform list
	
To tail logs, run

	platform logs deploy -e pr-426 --tail
	platform logs cron -e pr-426 --tail
	platform logs php -e pr-426 --tail
	
and make sure change `pr-426` with your PR.

To run drush on the remote environment, e.g. to enable the `devel` module

	platform drush -e pr-426 'en devel'
	
To get the URL of an environment, run

	platform url -e pr-426
 	
Or check out the current environments

	platform environments
	
which returns
	
	Your environments are: 
	+-----------+-------------------------------------------------------------------------+-------------+
	| ID        | Name                                                                    | Status      |
	+-----------+-------------------------------------------------------------------------+-------------+
	| master    | Master                                                                  | Active      |
	| develop*  | develop                                                                 | Active      |
	|    pr-421 | PR #421: PLAT-387: Single Message row updates                           | In progress |
	|    pr-426 | PR #426: Feature/plat 386 promo design up                               | In progress |
	|    pr-427 | PR #427: PLAT-383_fix_scheduled_update_test_on_travis testing on travis | In progress |
	|    pr-430 | PR #430: PLAT-400 Fix config:import                                     | Active      |
	|    pr-434 | PR #434: Adding more doc on platform.sh                                 | Active      |
	+-----------+-------------------------------------------------------------------------+-------------+
	* - Indicates the current environment

You can also re-install the Campaign profile if you'd like via Drush, for example if you made big changes to the profile configuration in the middle of a Pull Request. You can do this like

	platform ssh -e pr-426
	>> drush si cr -y --account-pass='admin' install_configure_form.update_status_module='[FALSE, FALSE]'
