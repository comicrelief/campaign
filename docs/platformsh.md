### Platform.sh

We use [platform.sh](http://platform.sh) for deploying Pull Requests to environments.

* Every time you create a new Pull Request, a new environment will be created and the Campaign profile will be installed *from scratch*
* When you keep pushing to that branch, the site won't be reinstalled again. You can still manually reinstall the site via the given Drush integration.
