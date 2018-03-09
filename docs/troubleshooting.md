## Information, hints, tips and troubleshooting


**Skip Circle CI builds**

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

**The service definition "renderer" does not exist in ContainerBuilder.php:796**

Following error is thrown during a build action:
```bash
Symfony\Component\DependencyInjection\Exception\InvalidArgumentException: The service definition "renderer" does not exist. in
/Users/vireshpatel/Documents/workspace/ComicRelief/campaign/vendor/symfony/dependency-injection/ContainerBuilder.php:796
Stack trace:
#0 /Users/vireshpatel/Documents/workspace/ComicRelief/campaign/vendor/symfony/dependency-injection/ContainerBuilder.php(440):
Symfony\Component\DependencyInjection\ContainerBuilder->getDefinition('renderer')
#1 /Users/vireshpatel/Documents/workspace/ComicRelief/campaign/core/lib/Drupal.php(158):
Symfony\Component\DependencyInjection\ContainerBuilder->get('renderer')
#2 /Users/vireshpatel/Documents/workspace/ComicRelief/campaign/core/includes/install.core.inc(1151): Drupal::service('renderer')
#3 /Users/vireshpatel/Documents/workspace/ComicRelief/campaign/core/includes/install.core.inc(1089): install_database_errors(Array, './sites/default...')
#4 /Users/vireshpatel/Documents/workspace/ComicRelief/campaign/core/includes/install.core.inc(366): install_verify_database_settings('sites/default')
#5 /Users/vireshpatel/Documents/workspace/ComicRelief/campaign/core/includes/install.core.inc(113):
install_begin_request(Object(Composer\Autoload\ClassLoader), Array)
#6 /usr/local/lib/drush/includes/drush.inc(726): install_drupal(Object(Composer\Autoload\ClassLoader), Array)
#7 /usr/local/lib/drush/includes/drush.inc(711): drush_call_user_func_array('install_drupal', Array)
#8 /usr/local/lib/drush/commands/core/drupal/site_install.inc(80): drush_op('install_drupal', Object(Composer\Autoload\ClassLoader), Array)
#9 /usr/local/lib/drush/commands/core/site_install.drush.inc(247): drush_core_site_install_version('cr', Array)
#10 /usr/local/lib/drush/includes/command.inc(366): drush_core_site_install('cr', 'Campaign')
#11 /usr/local/lib/drush/includes/command.inc(217): _drush_invoke_hooks(Array, Array)
#12 /usr/local/lib/drush/includes/command.inc(185): drush_command('cr', 'Campaign')
#13 /usr/local/lib/drush/lib/Drush/Boot/BaseBoot.php(67): drush_dispatch(Array)
#14 /usr/local/lib/drush/includes/preflight.inc(66): Drush\Boot\BaseBoot->bootstrap_and_dispatch()
#15 /usr/local/lib/drush/drush.php(12): drush_main()
#16 {main}
```

This usually happens when drush is unable to connect to the database. Check the following:
1. database server is running
2. database server connections and security/firewall configurations
3. database schema exists
4. database connection credentials and setup in database server

**Imagemagick**

As we're using the Imagemagick tookit now (allowing animated gifs to be used on the site), make sure you've got this installed locally. If not, you won't see any images and you'll see errors in your logs like

	ImageMagick error 127: sh: convert: command not found

Mac users can use the following quick commands (presuming Homebrew is installed):

	brew update
	brew install imagemagick --disable-openmp --build-from-source
	
Then, if you don't use MAMP but also Homebrew for your PHP, you need to enable imagemagick for PHP. This is for PHP 5.6

	brew install php56-imagick
	
Finally, it might make sense to add imagemagick binaries to `/usr/bin` in order not to have to configure this every time in the Drupal UI at `admin/config/media/image-toolkit`

	sudo ln -s /usr/local/bin/convert /usr/bin/convert
	sudo ln -s /usr/local/bin/identify /usr/bin/identify
	
Alternatively, you can also set the path to find imagemagick binaries like

	drush cset imagemagick.settings path_to_binaries /usr/local/bin/ -y
