## Information, hints, tips and troubleshooting


**How to run this on Pantheon**

Add Pantheon as a second remote:

```bash
git remote add pantheon ssh://codeserver.dev.f9291f1f-3819-4964-9c5b-c9f7d5500d28@codeserver.dev.f9291f1f-3819-4964-9c5b-c9f7d5500d28.drush.in:2222/~/repository.git
```

Now you can push to Pantheon to deploy this https://dashboard.pantheon.io/sites/f9291f1f-3819-4964-9c5b-c9f7d5500d28#dev/code

How to deal with settings.php on Pantheon https://pantheon.io/docs/articles/drupal/configuring-settings-php/

**Skip Travis CI builds**

Include the following within any commit message `[ci skip]`

**Grunt command not found**

If grunt errors with:

```bash
-bash: grunt: command not found
```

then install the grunt command line interface

```bash
npm install -g grunt-cli
```

If this fails due to permissions, check the rules for the '/usr/local/lib/node_modules' folder and/or run the above as sudo:

```bash
sudo chmod 777 /usr/local/lib/node_modules
sudo npm install -g grunt-cli
```

**Grunt and port 35729 in use**

If running grunt build fails with error:

```bash
Fatal error: Port 35729 is already in use by another process
```

then grab/check the Process ID set for the port is being used and release it:

```bash
sudo lsof -i :35729
kill -9 <PID>
```

**Ubuntu grunt:build error**

Error:
```bash
Property ${app.profile.theme} => profiles/cr/themes/custom/campaign_base
     [exec] Executing command: grunt build
/usr/bin/env: node: No such file or directory
```

Fix:
```bash
sudo ln -fs /usr/bin/nodejs /usr/local/bin/node
```
Note: Nodejs location may differ.

**Failed to open stream: No such file or directory Extension.php:145**

Appears on web browser when navigating to any page, e.g.:

```text
(/campaign/profiles/cr/modules/contrib/optimizely/optimizely.module): failed to open stream: No such file or directory Extension.php:145 [warning]
```

This usually happens when you've switched branches and the database hasn't been rebuilt. Simply perform either:

```bash
phing install
```

Or:

```bash
phing build:dev
```
