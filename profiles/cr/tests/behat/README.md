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
phing tests
```


## Standards

* Every feature should has the same name that the module to test.
* Add a tag* in order to make easy to test specific behavior.


## Tags

* @ajax : test ajax content
* @frontend : related with FE
* @backend : related with BE
* @anonymous : related with users without permissions
* @editor : only for CR members
* @admin : only for developers
