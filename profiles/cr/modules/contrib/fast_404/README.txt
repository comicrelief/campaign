Fast404 is Super Fast and Super Amazing. It is also very aggressive and
hard-core.
BE CAREFUL! TEST YOUR SITE THOROUGHLY AFTER ENABLING!


INSTALLATION INSTRUCTIONS

Basic Install *NOTE, THIS ONLY CHECKS STATIC FILES AND NOT DRUPAL PATHS*
(no settings.php modifications needed)
Step 1. Upload the module to your standard modules location (usually modules).
Step 2. Enable the module in your modules page

Advanced Install
Step 1. Upload the module to your standard modules location (usually
  modules).
Step 2. Place the code at the bottom of this file into your settings.php file
Step 3. Optionally, modify the include_once path if you did not put the module
  in /modules
Step 4. Enable the module in your modules page

-- Getting Extra Speed out of the Advanced Install --

#1) Check extensions from settings.php, not after loading all modules.

  This method is faster as it checks for missing static files at bootstrap
  stage 1 rather than 5 when the modules are loaded and events dispatched.

  To enable this functionality, uncomment the lines below near the bottom of the
  settings.php code:

  if (file_exists('./modules/fast404/fast404.inc')) {
    include_once './modules/fast404/fast404.inc';
    fast404_preboot($settings);
  }

#2) Enable Drupal path checking

  This checks to see if the URL you being visited actually corresponds to a
  real page in Drupal. This feature may be enabled with the following.

  Global switch to turn this checking on and off (Default: off)
    $settings['fast404_path_check'] = FALSE;


#3) Give the static file checking a kick in the pants!

  Static file checking does require you to keep an eye on the extension list
  as well as a bit of extra work with the preg_match (OK, a very small amount).
  Optionally, you can use whitelisting rather than blacklisting. To turn this
  on alter this setting in the settings.php:
  $settings['fast404_url_whitelisting'] = TRUE;

  This setting requires you to do some serious testing to ensure your site's
  pages are all still loading. Also make sure this list is accurate for your
  site:

  $settings['fast404_whitelist']  = array('index.php', 'rss.xml', 'install.php', 'cron.php', 'update.php', 'xmlrpc.php');

#4) Disallow imagecache file creation for anonymous users (NEW!)

  Normally the module skips out if 'styles' is in the URL to the static file.
  There are times when you may not want this (it would be pretty easy for
  someone to take down your site by simply hammering you with URLs with
  image derivative locations in them.

  In an ideal situation, your logged in users should have verified the pages
  are loading correctly when they create them, so any needed image derivatives
  are already made. This new setting will make it so that image derivative URLs
  are not excluded and fall under the same static file rules as non-imagecache
  URLs. Set to false to enable this new feature.

  $conf['fast404_allow_anon_imagecache'] = TRUE;

#5) Prevent conflicts with other modules

  Some performance modules create paths to files which don't exist on disk.
  These modules conflict with fast404.  To workaround this limitation, you
  can whitelist the URL fragments used by these modules.

  For example if you are using the CDN module and have the far future date
  feature enabled add the following configuration:

  $settings['fast404_string_whitelisting'] = array('cdn/farfuture');

  If you are using AdvAgg you can use this:
  $settings['fast404_string_whitelisting'] = array('/advagg_');

  Any further modules/paths that may need whitelisting can be added to the array.


-----------------------------------------------------------------
--- Copy the code below into the BOTTOM of your settings.php. ---
--- If you are using cacherouter, put this above cacherouter. ---
-----------------------------------------------------------------

/**
 * Fast 404 settings:
 *
 * Fast 404 will do two separate types of 404 checking.
 *
 * The first is to check for URLs which appear to be files or images. If Drupal
 * is handling these items, then they were not found in the file system and are
 * a 404.
 *
 * The second is to check whether or not the URL exists in Drupal by checking
 * with the menu router, aliases and redirects. If the page does not exist, we
 * will server a fast 404 error and exit.
 */

 // @TODO anything still using $conf hasn't yet been implemented within the module.

# Disallowed extensions. Any extension in here will not be served by Drupal and
# will get a fast 404. This will not affect actual files on the filesystem as
# requests hit them before defaulting to a Drupal request.
# Default extension list, this is considered safe and is even in queue for
# Drupal 8 (see: http://drupal.org/node/76824).
$settings['fast404_exts'] = '/^(?!robots).*\.(txt|png|gif|jpe?g|css|js|ico|swf|flv|cgi|bat|pl|dll|exe|asp)$/i';

# If you use a private file system use the conf variable below and change the
# 'sites/default/private' to your actual private files path
# $settings['fast404_exts'] = '/^(?!robots)^(?!sites/default/private).*\.(txt|png|gif|jpe?g|css|js|ico|swf|flv|cgi|bat|pl|dll|exe|asp)$/i';

# If you would prefer a stronger version of NO then return a 410 instead of a
# 404. This informs clients that not only is the resource currently not present
# but that it is not coming back and kindly do not ask again for it.
# Reference: http://en.wikipedia.org/wiki/List_of_HTTP_status_codes
# $conf['fast_404_return_gone'] = TRUE;

# Allow anonymous users to hit URLs containing 'imagecache' even if the file
# does not exist. TRUE is default behavior. If you know all imagecache
# variations are already made set this to FALSE.
$settings['fast404_allow_anon_imagecache'] = TRUE;

# If you use FastCGI, uncomment this line to send the type of header it needs.
# Reference: http://php.net/manual/en/function.header.php
# $conf['fast_404_HTTP_status_method'] = 'FastCGI';

# BE CAREFUL with this setting as some modules
# use their own php files and you need to be certain they do not bootstrap
# Drupal. If they do, you will need to whitelist them too.
$conf['fast404_url_whitelisting'] = FALSE;

# Array of whitelisted files/urls. Used if whitelisting is set to TRUE.
$settings['fast404_whitelist'] = array('index.php', 'rss.xml', 'install.php', 'cron.php', 'update.php', 'xmlrpc.php');

# Array of whitelisted URL fragment strings that conflict with fast404.
$settings['fast404_string_whitelisting'] = array('cdn/farfuture', '/advagg_');

# By default we will show a super plain 404, because usually errors like this are shown to browsers who only look at the headers.
# However, some cases (usually when checking paths for Drupal pages) you may want to show a regular 404 error. In this case you can
# specify a URL to another page and it will be read and displayed (it can't be redirected to because we have to give a 30x header to
# do that. This page needs to be in your docroot.
#$conf['fast404_HTML_error_page'] = './my_page.html';

# Path checking. USE AT YOUR OWN RISK.
# Path checking at this phase is more dangerous, but faster. Normally
# Fast404 will check paths during Drupal bootstrap via an early Event.
# While this setting finds 404s faster, it adds a bit more load time to
# regular pages, so only use if you are spending too much CPU/Memory/DB on
# 404s and the trade-off is worth it.
# This setting will deliver 404s with less than 2MB of RAM.
#$settings['fast404_path_check'] = TRUE;

# Default fast 404 error message.
$settings['fast404_html'] = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML+RDFa 1.0//EN" "http://www.w3.org/MarkUp/DTD/xhtml-rdfa-1.dtd"><html xmlns="http://www.w3.org/1999/xhtml"><head><title>404 Not Found</title></head><body><h1>Not Found</h1><p>The requested URL "@path" was not found on this server.</p></body></html>';

# Load the fast404.inc file. This is needed if you wish to do extension
# checking in settings.php.
// if (file_exists('./modules/fast404/fast404.inc')) {
//   include_once './modules/fast404/fast404.inc';
//   fast404_preboot($settings);
// }
