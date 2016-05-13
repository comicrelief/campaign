EU Cookie Compliance 8.x - 1.x
==============================

This module intends to deal with the EU Directive on Privacy and Electronic
Communications that comes into effect on 26th May 2012.
From that date, if you are not compliant or visibly working towards compliance,
you run the risk of enforcement action, which can include a fine of up to
half a million pounds for a serious breach.

How it works.
=============

The module displays a pop-up at the bottom or at the top of website to make
users aware of the fact that cookies are being set. The user may then give
his/her consent or move to a page that provides more details. Consent is given
by user pressing the agree buttons or by continuing browsing the website. Once
consent is given another pop-up appears with a thank you message.

The module provides a settings page where the pop-up can be customized. There
are also template files for the pop-ups that can be overridden by your theme.

Installation.
=============

1. Unzip the files to the "sites/all/modules" OR "modules" directory and enable the module.

2. If desired, give the administer EU Cookie Compliance pop-up permissions that
allow users of certain roles access the administration page. You can do so on
the admin/user/permissions page.

  - there is also a 'display EU Cookie Compliance popup' permission that helps
    you show the popup to the roles you desire.

3. You may want to create a page that would explain how your site uses cookies.
Alternatively, if you have a privacy policy, you can link the pop-up to that
page (see next step).

4. Go to the admin/config/system/eu-cookie-compliance page to configure and enable
the pop-up.

5. If you want to customize the pop-up background and text color, either type
in the hex values or simply install http://drupal.org/project/jquery_colorpicker.

6. If you want to theme your pop-up override the themes in the template file.

7. If you want to show the message in EU countries only, install the geoip
module: http://drupal.org/project/geoip and enable the option on the admin page.

NOTICE: The module does not audit your cookies nor does it prevent cookies
from being set.

For developers.
===============

If you want to conditionally set cookies in your module, there is a javascript
function provided that returns TRUE if the current user has given his consent:

Drupal.eu_cookie_compliance.hasAgreed()
