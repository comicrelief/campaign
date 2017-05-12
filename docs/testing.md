## Testing instructions

### Installation
```bash
brew install selenium-server-standalone
brew install chromedriver
```
Now, launch selenium
```bash
selenium-server -port 4444
```
if you are using our docker stack you can check it out here: http://localhost:4445/wd/hub/

### Running tests
```bash
vendor/bin/behat
```
For more info http://behat.org/en/latest/user_guide/command_line_tool/identifying.html
