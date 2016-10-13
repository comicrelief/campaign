Installation
------------

### Installing the YAML Form Module

1. Copy/upload the yamlform.module to the modules directory of your Drupal
   installation.

2. Enable the 'YAML Form' module and desired [sub-modules](#sub-modules) in 'Extend'. 
   (/admin/modules)

3. Setup user permissions. (/admin/people/permissions#module-yamlform)

4. Build a new form (/admin/structure/yamlform)
   or duplicate an existing template (/admin/structure/yamlform/templates).
   
5. Publish your form as a:

    - **Page:** By linking to the published form.
      (/yamlform/contact)  

    - **Node:** By creating a new node that references the form.
      (/node/add/yamlform)

    - **Block:** By placing a YAML Form block on your site.
      (/admin/structure/block)

6. (optional) Install [third party libraries](#third-party-libraries).

7. (optional) Install [additional contrib modules](#additional-contrib-modules).


### Sub Modules

**YAML Form UI**

The YAML Form UI module provides a simple user interface for building and 
maintaining forms.

> Unless your website is maintained by experienced Drupal developers, 
> every website should enable the YAML Form UI module. 

**YAML Form Templates**

The YAML Form Templates module provides starter templates that can be used 
to create new forms. 

> Besides using the provided default starter templates, you can also create 
> custom templates for your organization.

**YAML Form Node**

The YAML Form Node module provides a 'Form' content type, which allows  
forms to be integrated into a website as nodes.

> The YAML Form Node module creates a form (entity reference) field
> that allows any form to be attached to any content type.  

**YAML Form Examples**

The YAML Form Examples module provides examples of all available form elements 
and functionality, which can used for demonstrating and testing advanced 
functionality or used as cut-n-paste code snippets for creating new forms.

> The YAML Form Examples module allows site builders and developers to 
> preview and experiment with working examples of all supported form elements 
> and features.

### Additional Contrib Modules 

When installed, the modules below will enhance your website's form building and 
submission handling functionality and experience.

**[YAML Form Queue](https://www.drupal.org/project/token)**

The [YAML Form Queue](https://www.drupal.org/project/token) module provides a 
queue handler for YAML Form, to store form submissions in a queue.

**[Token](https://www.drupal.org/project/token)**

The [Token](https://www.drupal.org/project/token) module provides additional 
tokens not supported by core (most notably fields), as well as a UI for browsing 
tokens.

> Tokens are supported and actively used by the YAML Form module. Installing the
> Token module will provide form builders with the ability to browse form and 
> submission specific tokens. 

**[Mail System](https://www.drupal.org/project/mailsystem) and [Swift Mailer](https://www.drupal.org/project/swiftmailer)**

The [Mail System](https://www.drupal.org/project/mailsystem) module provides an 
Administrative UI and Developers API for managing the mail backend/plugin.
 
The [Swift Mailer](https://www.drupal.org/project/swiftmailer) extends the 
basic e-mail sending functionality provided by Drupal by delegating all e-mail
handling to the Swift Mailer library.

> The YAML Form module provide support for HTML email, however to send file 
> attachments, please install and configure the 
> [Mail System](https://www.drupal.org/project/mailsystem) and 
> [Swift Mailer](https://www.drupal.org/project/swiftmailer) modules.

**[Honeypot](https://www.drupal.org/project/honeypot)**

The [Honeypot](https://www.drupal.org/project/honeypot) module uses both the 
honeypot and timestamp methods of deterring spam bots from completing forms on 
your Drupal site.

> The Honeypot module provides the best unobtrusive protection against SPAM form submissions.

**[CAPTCHA](https://www.drupal.org/project/captcha) and [reCAPTCHA](https://www.drupal.org/project/recaptcha)**

The [CAPTCHA](https://www.drupal.org/project/captcha) module provides the 
CAPTCHA API for adding challenges to arbitrary forms.

The [reCAPTCHA](https://www.drupal.org/project/recaptcha) module uses the
[Google reCAPTCHA](https://www.google.com/recaptcha/intro/index.html) web 
service to improve the CAPTCHA system, and to protect email addresses.

> CAPTCHA provides additional, slightly obtrusive protection against SPAM 
> submissions.

**[Validators](https://www.drupal.org/project/validators)**

The [Validators](https://www.drupal.org/project/validators) module allows you 
to use the Symfony Validator component within a form.

> The Validators module is one of the YAML Form module's supported 
> validation mechanisms.

### Third Party Libraries

The YAML Form module utilizes the third-party Open Source libraries below to 
enhance form elements and to provide additional functionality.  It is recommended 
that these libraries be installed in your Drupal installations /libraries 
directory.  If these libraries are not installed, they are automatically loaded 
from a CDN.

> PLEASE NOTE: The 
> [Libraries API](https://www.drupal.org/project/libraries) for Drupal 8 is 
> still under development.  

Currently the best way to download all the needed third party libraries is to 
either add [yamlform.libraries.make.yml](http://cgit.drupalcode.org/yamlform/tree/yamlform.libraries.make.yml)
to your drush make file or execute below drush command from the root of your 
Drupal installation.  

    drush yamlform-libraries-download    

**[Code Mirror](http://codemirror.net/)** - [Demo](http://codemirror.net/)

A versatile text editor implemented in JavaScript for the browser.

> Code Mirror is used to provide a text editor for YAML and HTML configuration
> settings and messages.

**[Geocomplete](https://ubilabs.github.io/geocomplete/)** - [Demo](http://ubilabs.github.io/geocomplete/examples/form.html)

An advanced jQuery plugin that wraps the Google Maps API's Geocoding and Places Autocomplete services.

> Geocomplete is used by the location element.

**[Input Mask](http://robinherbots.github.io/jquery.inputmask/)** - [Demo](http://robinherbots.github.io/jquery.inputmask/)

Input masks ensures a predefined format is entered. This can be useful for 
dates, numerics, phone numbers, etc...

> Input masks are used to ensure predefined and custom format for text fields.

**[RateIt](https://github.com/gjunge/rateit.js)** - [Demo](http://gjunge.github.io/rateit.js/examples/)

Rating plugin for jQuery. Fast, Progressive enhancement, touch support, 
customizable (just swap out the images, or change some CSS), Unobtrusive 
JavaScript (using HTML5 data-* attributes), RTL support, supports as many stars 
as you'd like, and also any step size.

> RateIt is used to provide a customizable rating form element.

**[Select2](https://select2.github.io/)** - [Demo](https://select2.github.io/examples.html)

Select2 gives you a customizable select box with support for searching and 
tagging.

> Select2 is used to improve the user experience for select menus.

**[Signature Pad](https://github.com/szimek/signature_pad)** - [Demo](http://szimek.github.io/signature_pad/)

Signature Pad is a JavaScript library for drawing smooth signatures. It's HTML5 
canvas based and uses variable width Bézier curve interpolation 
It works in all modern desktop and mobile browsers and doesn't depend on any 
external libraries.

> Signature Pad is used to provide a signature element.

**[Word and Character Counter](https://github.com/qwertypants/jQuery-Word-and-Character-Counter-Plugin)** - [Demo](http://qwertypants.github.io/jQuery-Word-and-Character-Counter-Plugin/)

The jQuery Word and character counter plug-in allows you to count characters
or words.

> Word or character counting, with server-side validation, is available for text 
> fields and text areas.

**[CKEditor](http://ckeditor.com/)**

The standard version of the CKEditor.

> Allows the YAML Form module to implement a basic and simpler CKEditor.
