
Table of Contents
-----------------

 * [About this module](#about-this-module)
 * [Demo](#demo)
 * [Goals](#goals)
 * [Concepts](#concepts)
 * [Example](#example)
 * [Features](#features)
 * [Security](#security)
 * [Installation](#installation) 
 * [Releases](#releases)  
 * [Extending](#extending)
 * [Troubleshooting](#troubleshooting)
 * [Notes](#notes)
 * [References/Related Projects](#references-related-projects)


About this Module
-----------------

The YAML form module is a lightweight, fast, **developer centric**, 
FAPI based form builder and submission  manager for Drupal 8.
   
The primary use case for this module is...

- Admin duplicates and customizes an existing form/template.
- Admin tests and publishes the new form.
- User fills in and submits the form.
- Submission is stored in the database.
- Admin receives an email notification. 
- User receives an email confirmation.
- Admin views submissions online.
- Admin downloads all submissions as a CVS.

Don't use this module if you...

- Want [Views](https://www.drupal.org/node/1912118) integration.
- Prefer drag-n-drop form builders.
- Have 'untrusted' users building forms. 

Use this module if you...

- Understand [Render Arrays](https://www.drupal.org/developing/api/8/render/arrays),
  [FAPI](https://www.drupal.org/node/2117411),
  and [YAML](https://en.wikipedia.org/wiki/YAML).
- Prefer
  [CLI](https://en.wikipedia.org/wiki/Command-line_interface)
  over [GUI](https://en.wikipedia.org/wiki/Graphical_user_interface).
- Have 'trusted' developers building and customizing forms. 

Additional things to consider...

- Results can be exported as a CVS for use in 
  [Google Sheets](https://www.google.com/sheets/about/)
  or [Excel](https://products.office.com/en-us/excel).
- The alternatives: 
  [Contact](https://www.drupal.org/documentation/modules/contact) with 
  [Contact Storage](https://www.drupal.org/documentation/modules/contact_storage),
  [Eform](https://www.drupal.org/project/eform),
  [Webform](https://www.drupal.org/project/webform),
  [Form.io](https://form.io/),
  [Google Forms](https://www.google.com/forms/about/),
  [SurveyMonky](https://www.surveymonkey.com)
  [Webform.com](https://www.drupal.org/project/webform),
  [Wufoo](http://www.wufoo.com/),
  [etc...](https://www.google.com/search?q=Form+builders)
- How can you help extend and improve this module?


Demo
----

> Evaluate this project online using [simplytest.me](https://simplytest.me/project/yamlform).

> [Watch a demo](http://youtu.be/ycWUPAoSfT4) of the YAML form module.


Goals
-----

- A stable, maintainable, and tested API for building forms and submission handling. 
- A pluggable/extensible API for custom submission handling. 
- A focused and limited feature set to ensure maintainability. 


Concepts
--------

- What is the simplest and fastest way to create a form builder and submission
  manager in Drupal 8?
- How much can Drupal 8's Form and Entity API be leveraged to
  build a form builder and submission manager?
- Building and maintaining a form building UI consumes a lot of time 
  and resources.
- YAML as a markup language is simple... so simple it could be presented as the 
  UI for building and maintaining forms in Drupal.
- Replace complexity with extensibility


Example
-------

Here is an example of a contact form's render array written in YAML.

    name:
      '#title': 'Your Name'
      '#type': textfield
      '#required': true
    email:
      '#title': 'Your Email'
      '#type': email
      '#required': true
    subject:
      '#title': 'Subject'
      '#type': textfield
      '#required': true
    message:
      '#title': 'Message'
      '#type': textarea
      '#required': true


Features
--------
    
**Forms** 

- Create forms using Drupal's Form API (FAPI)
- Custom confirmation page, message, and redirection
- Reusable and customizable list of common select menu, radios, and checkbox 
  options. This includes countries, states, etc...
- Reuse options for autocompletion
- Starter templates for common forms
- Ability to duplicate existing forms and templates
- Prepopulate form with querystring parameters
- Set custom URL aliases for the form and its confirmation page
- Preview and save draft support

**Inputs**

- Support for every
  [form element](https://api.drupal.org/api/drupal/developer!topics!forms_api_reference.html/8)
  included in Drupal 8 core. This includes file uploads and entity references.
- Conditional logic using [FAPI States API](https://api.drupal.org/api/examples/form_example%21form_example_states.inc/function/form_example_states_form/7)
- Input masks (using [jquery.inputmask](https://github.com/RobinHerbots/jquery.inputmask))
- Private inputs/elements
- Define optional display format for individual inputs. 
    - For example, checkboxes can display as comma delimited value or a bullet list.
- Support for form functionality and elements provided by contrib modules 
  including
  [Honeypot](https://www.drupal.org/project/honeypot),
  [Mollom](https://www.drupal.org/project/mollom),
  [CAPTCHA](https://www.drupal.org/project/captcha),
  [Elements](https://www.drupal.org/project/elements),
  [Select (or other)]( https://www.drupal.org/project/select_or_other),
  and more...

**Submissions/Results**

- View submissions as HTML, plain text, and YAML
- Download results as a CSV to Google Sheets or MS Excel
- Fine grain access control by roles and user
- Users can view previous submissions
- Limit total number of submissions or user specific submissions
- Drush support for exporting CVS and purging submissions

**Emails/Handlers**

- Extensible form submission handler plugin  
- Handles email notifications and confirmations 
- Preview and resend emails
- HTML email (does not require any additional modules)
- File attachments (requires [Mail System](https://www.drupal.org/project/mailsystem), only [Swift Mailer](https://www.drupal.org/project/swiftmailer) has been tested) 

**Third Party Settings**

- Allows contrib modules to define additional settings and behaviors that can be 
  applied to all YAML forms or just one specific YAML form.
  
**Integration**

- Block integration
- Node integration
- Token support
- YAML and HTML source editor using [CodeMirror](https://codemirror.net/)

**Internationalization**

- Translation integration
- Tracks submission language

**Development**

- Generate test submissions using devel generate and customizable test datasets


Security
--------

This module allows developers to have full access to Drupal's Render API,
this includes the ability to set [callbacks](http://php.net/manual/en/language.types.callable.php),
which are PHP functions that are executed during the rendering process.
This means anyone who can administer and build a YAML form can call any PHP code
on your website.

> Only the most trusted users should be granted permission to administer and
  build YAML forms.

_Please note: Administering and exporting a YAML form's results is a dedicated
and secure role._
 

Installation
------------

1. Copy/upload the yamlform.module to the modules directory of your Drupal
   installation.
2. (optional) Install [CodeMirror](http://codemirror.net/) and 
    [jquery.inputmask](https://github.com/RobinHerbots/jquery.inputmask)
    using [Composer Manager](https://www.drupal.org/project/composer_manager).
   If CodeMirror and/or jquery.inputmask is not installed, they will be loaded 
   from <https://cdnjs.com/>.
3. Enable the 'YAML form' module in 'Extend'. (/admin/modules)
4. Setup permissions. (/admin/people/permissions#module-yamlform)
5. Begin building a new YAML form or duplicate an existing one.
   (/admin/structure/yamlform)
6. Publish your new YAML form as a...
    - **Page:** By linking to the published YAML form.
      (/yamlform/contact)  
    - **Node:** By creating a new node that references the YAML form.
      (/node/add/yamlform)
    - **Block:** By placing a YAML form block on your site.
      (/admin/structure/block)

Notes

- Tokens are supported and actively used by the YAML form module. 
  It is recommended that the  
  [Token module](https://www.drupal.org/project/token) is installed.
- For email file attachment support please install and configure the 
  [Mail System](https://www.drupal.org/project/mailsystem) and 
  [Swift Mailer](https://www.drupal.org/project/swiftmailer) modules.


Releases
--------

Even though, the YAML form module is still under active development with
regular [alpha releases](https://www.drupal.org/documentation/version-info/alpha-beta-rc)
all YAML form configuration and submission data will be maintained and updated 
between releases.  **APIs can and will be changing** while this module moves to 
a beta release and finally a release candidate. The beta release of the 
YAML form module (hopefully before or after DrupalCon New Orleans) 
will be tied to a feature freeze.

Simply put it, if you install and use the YAML form module AS-IS, out of the box, 
you _should_ be okay.  Once you start extending YAML forms with plugins, alter 
hooks, and template overrides, you will need to read the release notes and 
assume _things will be changing_.


Extending
---------

YAML form provides a YamlFormHandler [plugin](https://www.drupal.org/developing/api/8/plugins)
as well as support for third party settings and alter hooks for contrib modules
to extend and enhance a YAML form.

**YamlFormHandler plugin**

The YamlFormHandler plugin allows developers to extend a YAML form's inputs 
and submission handling. Each YamlFormHandler plugin should live in a dedicated 
module and handler namespace. For example, if a developer wanted to setup 
MailChimp integration, they would create the YAML form MailChimp module 
(yamlform_mailchimp.module) which would contain the YamlFormMailChimpHandler.

This approach/pattern will allow any popular YamlFormHandler plugins
(that include tests) to be easily contributed back to the main YAML form module.
          
**Third Party Settings**

The YAML form module allows contrib modules to also set third party settings
for all YAML forms and/or one specific YAML form.  

See the yamlform_test_third_party_settings.module for an example of how a
contrib can use third party settings to extend and enhance a YAML form.

Notes
-----

- Input names will be used to store data.
- Duplicate input names are not allowed.
- The [#tree](https://api.drupal.org/api/drupal/developer!topics!forms_api_reference.html/8#tree) 
  property, which is used to allow collections of form elements, is not allowed.
- Element callback properties are not supported within a YAML input.
  This includes `#element_validate`, `#after_build`, `#post_render`, `#pre_render`, `#process`, `#submit`, `#value_callback`, and `#validate`.
- Once there has been a form submission, existing input names should never be
  deleted, they can be be hidden (via `'#access': false`).


Troubleshooting
---------------

**How to debug issues with YAML form inputs/elements?**

- A YAML form's input data is just a [Form API(FAPI)](https://www.drupal.org/node/37775)
  [render array](https://www.drupal.org/developing/api/8/render/arrays). 
- Some issues can be fixed by reading the API documentation associated 
  with a given [form element](https://api.drupal.org/api/drupal/core%21lib%21Drupal%21Core%21Render%21Element%21FormElement.php/class/FormElement/8).

**How to get help fixing issues with the YAML form module?**

- Review the YAML form module's [issue queue](https://www.drupal.org/project/issues/yamlform) 
  for similar issues.
- If you need to create a new issue, **please** create and export an example of 
  the broken YAML form configuration.   
  _This will helps guarantee that your issue is reproducible._  
- Please also read [How to create a good issue](https://www.drupal.org/issue-queue/how-to)
  and use the [Issue Summary Template](https://www.drupal.org/node/1155816)
  when creating new issues.


References/Related Projects
---------------------------

- [YAML](http://www.yaml.org/start.html)
- [Comparison of Form Building Modules](https://www.drupal.org/node/2083353)
- [Contact](https://www.drupal.org/documentation/modules/contact) 
    - [Contact Storage](https://www.drupal.org/documentation/modules/contact_storage)
    - [Contact module 8.1 and beyond roadmap](https://www.drupal.org/node/2582955)
    - [Goodbye Webform? Contact Forms Are In the Drupal 8 Core](https://www.ostraining.com/blog/drupal/drupal-8-contact-forms/)
- [Eform](https://www.drupal.org/project/eform)
    - [When to use Entityform](https://www.drupal.org/node/1540680)
- [Webform](https://www.drupal.org/project/webform) 
    - [Port Webform to Drupal 8](https://www.drupal.org/node/2075941)


Author/Maintainer
-----------------

- [Jacob Rockowitz](http://drupal.org/user/371407)
