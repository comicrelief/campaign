## Behat tests

To set up Behat tests to run locally, run:
```
	composer install
```

Finally, run the Behat command:
```
	vendor/bin/behat
```

Or run in the webroot for execute all tests:
```
phing test
```


## Standards

* Every feature should has the same name that the module to test.
* Add a tag* in order to make easy to test specific behavior.
* Tests should be able to run more twice without reinstall the site, so
have this in mind when you edit fixture content.

## Tags

* @ajax : test ajax content
* @frontend : related with FE
* @backend : related with BE
* @anonymous : related with users without permissions
* @editor : only for CR members
* @admin : only for developers
