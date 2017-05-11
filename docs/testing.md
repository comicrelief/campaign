## Testing instructions

_Warning: the documentation is still relying on Phing, which has since been removed_

### Installation

	brew install selenium-server-standalone
	brew install chromedriver
	
Now, launch selenium

	selenium-server -port 4444
	
### Running tests

	phing test
	
### List step definitions

  phing test:dl
  or
  ./vendor/bin/behat -dl

### Typical Behat steps

Try adding `And I break` to your step definitions to see tests interactively. Don't forget also to add `@javascript` so the scenario runs using Selenium

To just target a specific test, run this from within `profiles/cr/tests/behat`

	/vendor/bin/behat features/article.feature --tags="@javascript"
