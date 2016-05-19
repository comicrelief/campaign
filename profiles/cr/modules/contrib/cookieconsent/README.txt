CONTENTS OF THIS FILE
---------------------

 * Introduction
 * Requirements
 * Installation
 * Configuration
 * Maintainers

INTRODUCTION
------------
The CookieConsent is a module that provides a solution to deal with the EU Cookie Law. It integrates the
Cookie Consent javascript plugin (https://silktide.com/tools/cookie-consent/) to provide an easy, lightweight solution.

 * For a full description of the module, visit the project page:
   https://drupal.org/project/cookieconsent

 * To submit bug reports and feature suggestions, or to track changes:
   https://drupal.org/project/issues/cookieconsent

NOTICE: The module does not audit your cookies nor does it prevent cookies
from being set.

REQUIREMENTS
------------

No modules are required for this module.
This module uses version 2.0.9 of the Cookie Consent Javascript plugin, which is provided within this module.

INSTALLATION
------------

 * Install as you would normally install a contributed Drupal module. See:
   https://drupal.org/documentation/install/modules-themes/modules-8
   for further information.

CONFIGURATION
-------------

 * Configure user permissions in People » Permissions:

   - Administer cookieconsent settings

     Users in roles with the "Administer cookieconsent settings" permission can alter the appearance of the cookie
     popup.

 * Customize the cookie settings in Configuration » System » Cookie settings

 * A default template (templates/cookieconsent.html.twig) is provided. Overwrite this template file in your theme to
   alter the HTML of the cookie popup.

MAINTAINERS
-----------

Current maintainers:
 * Hans van Wezenbeek (Nitebreed) - https://drupal.org/user/419423