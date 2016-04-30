This is a step-by-step guide for manually testing and reviewing every feature,
role/permission, and use case provided by the YAML form module.

# Notes

- Contact form will be used for most examples.
- Incognito tab is the quickest way to test anonymous access to a form/feature.

--------------------------------------------------------------------------------

# Installation

- Update settings.php to allow the yamlform_test.module to be installed.  
  `$settings['extension_discovery_scan_tests'] = TRUE;`
- Enable the YAML form and YAML form test module.

--------------------------------------------------------------------------------

# Setup 

See: development-tools.md for commands.

- Create test roles and users.
- Create test submissions.


--------------------------------------------------------------------------------

# Administration

**Forms (/admin/structure/yamlform)**

- Review forms provided by the yamlform.module and yamlform_test.module
- Test filter
    - Filter a title, description, and/or inputs (which is not visible)
- Test sorting
- Inputs validation
    - Required: Leave blank
    - Valid YAML: Enter invalid YAML
    - Is array: Enter simple string value
    - Duplicate inputs name: Enter two inputs with the same names
    - Ignore properties: Enter input with #tree, #submit, etc... properties.
        
**Results** 

Manage (/admin/structure/yamlform/results/manage)

- Review submissions
- Test filter
    - Filter by submission data (which is not visible)
- Test sorting

Purge (/admin/structure/yamlform/results/purge)

- Test purging less than 1000 submissions without batch processing.
- Test purging more than 1000 submissions with batch processing.

Admin settings (/admin/structure/yamlform/settings)

- Review default values
- Review labels and descriptions
- Test updating values
- Test optional token support

**YAML form options (/admin/structure/yamlform/settings/options/manage)**

- Review default YAML form options
- Create, update, and delete YAML form options

**YAML form elements (/admin/structure/yamlform/settings/elements)**

- Review YAML form element titles and descriptions.

**YAML form handlers (/admin/structure/yamlform/settings/handlers)**

- Review YAML form handler titles and descriptions.


--------------------------------------------------------------------------------

# Forms

**(/admin/structure/yamlform)**

**Create new YAML form (/admin/structure/yamlform/add)**

- Check default inputs
- Test duplicate link
- Message about previous submissions should be displayed

**Duplicate existing YAML form (/admin/structure/yamlform/manage/template_registration/duplicate)**

- Duplicate a 'Template: Registration' form.
- Confirm all inputs and settings are duplicated.

**Test YAML form inputs (/yamlform/example_inputs/test)**

- Test 'kitchen sink' list of supported inputs
- Verify each input collects and displays submitted data correctly

**Test YAML form inputs with custom inputs (/yamlform/example_inputs_formats/test)**

- Test inputs have customized formats
- Verify HTML and text display for submitted data

**YAML form (/yamlform/contact)**

- Check system path (/yamlform/contact)
- Check submit alias (/form/contact)
- Check confirmation alias (/form/contact/confirmation)
     - This page is not used by the form which redirects to the homepage.

**YAML form settings (/admin/structure/yamlform/manage/contact/settings)**

- Review form
- Review hide/show logic (ie #States API).
- Review default values
- Review help text

**YAML form access (/admin/structure/yamlform/manage/contact/access)**

See: Access Rules 

**YAML form handlers (/admin/structure/yamlform/manage/contact/handlers)**

- Test contact form handlers
- Create, update, and delete YAML form handler
- Check that cardinality is support
    - Email handler support multiple instances
    - Null and Test handler only support a single instance
- Test disabling a handler

**YAML form email handlers (/admin/structure/yamlform/manage/contact/handlers)**

- Test email handlers
- Test debug setting


--------------------------------------------------------------------------------

# Third Party Settings

**YAML form global third party settings** (/admin/structure/yamlform/manage/contact/third-party)

- Check no modules installed message
- Enable yamlform_test_third_party_settings.module
- Add global message
- Check global message is display on Contact form (/yamlform/contact)

**YAML form specific third party settings** (/admin/structure/yamlform/manage/contact/third-party)

- Check no modules installed message
- Enable yamlform_test_third_party_settings.module
- Add message
- Check form specific message is display on Contact form (/yamlform/contact)



--------------------------------------------------------------------------------

# Results

**Submissions (/admin/structure/yamlform/manage/contact/results/submissions)**

- Check submission columns
- Check sorting
- Test filter
    - Filter by submission data (which is not visible)

**Table (/admin/structure/yamlform/manage/contact/results/table)**

- Check inputs columns (which are not sortable)
- Check 'Example: Inputs'
     - admin/structure/yamlform/manage/example_inputs/results/table

**Download (/admin/structure/yamlform/manage/contact/results/download)**

- Unchecking 'Download CSV' allows you to view the CVS as plain text.
     - 'Download CSV' is only available when there is less than 1000 submissions.

**Clear (/admin/structure/yamlform/manage/contact/results/clear)**

- Test clearing submissions.

**Submission (/admin/structure/yamlform/manage/contact/results/submissions)**

View tab

- Test submission navigation

HTML

- View submission as HTML

Plain text

- View submission as plain text

Data (YAML)

- View submission as data (YAML)

Edit tab

- Edit submission
- Simple confirmation message should always be displayed

--------------------------------------------------------------------------------

# Features

**Inputs**

- All inputs (/yamlform/test_inputs)
- Date inputs (/yamlform/test_inputs_dates)
- Entity autocomplete (/yamlform/test_inputs_entity_autocomplete)
    - Preview is broken and throwing serialization error.
- Text format (/yamlform/test_inputs_text_format)
- Ignored properties (/yamlform/test_inputs_ignored_properties)

**Closed (/yamlform/test_form_closed)**

- Check that form is closed for anonymous user
- Check that form is available to admin user but displays are warning.

**Prepopulate (/yamlform/test_form_prepopulate)**

- Check that name is prepopulated using query string variable.

**Submit text (/yamlform/test_form_submit_text)**

- Check that submit text is customized.

**Preview (/yamlform/test_preview)**

- Check custom preview and next submission buttons.
- Check custom preview message.
- Check optional and required preview.

**Draft (/yamlform/test_draft)**

- Check saving and reloading draft
- Check autosave occurs with validation errors.
- Check autosave occurs when previewing.

**Confirmation**

- Inline (/yamlform/test_confirmation_inline)
- Message (/yamlform/test_confirmation_message)
- Page (/yamlform/test_confirmation_page)
- URL (/yamlform/test_confirmation_url)

**Limits (/yamlform/test_limit)**

- Check only 1 submission is allowed for authenticated user.
- Check only 3 submission are allowed for all users.
- Check that admin can post new submission if their limit has no been reached.

**Results Disabled (/yamlform/test_results_disabled)**

- Check that results can be disabled. (Applies to users)

**Private access (/yamlform/private)**

- Check that private input is only accessible to submission administrators.


--------------------------------------------------------------------------------

# Access Rules

- Create test roles and users. (See NOTES.txt)

**Account/Roles**

- developer: Administer YAML forms and YAML form submissions.
- admin: Administer YAML form submissions.
- manager: Used to test managing a YAML form's submissions.
- user: Used to test accessing and managing one's own submission.

**Check 'developer' role**

- Already done via the above tests.

**Check 'admin' role**

- Login as admin/admin.
- Check that all forms and submission are accessible (/admin/structure/yamlform)
- Confirm that managing form is disabled. This includes..
    - Add form (/admin/structure/yamlform/add)
    - Edit form (/admin/structure/yamlform/manage/contact)
    - Delete form (/admin/structure/yamlform/manage/contact/delete)
- Purging all submissions is also disabled. (/admin/structure/yamlform/results/purge)
    - Only developer can purge all submissions.

**Check 'manager' role**

- As an 'admin' or 'developer' grant the 'manager' role access and manager 
  any submissions to a form.
  (/admin/structure/yamlform/manage/contact/access)
    - Assigning the 'manager' role to some permissions and the 'manager' user
      to other permissions, this will test both role and user based access rules.
- Login as manager/manager.
- Check CRUD operations on 'Contact' form (/admin/structure/yamlform/manage/contact)
- Check navigating between all submissions.

**Check 'user' role**

- As an 'admin' or 'developer' grant the 'user' role access and manager own 
  submission to a form.
  (/admin/structure/yamlform/manage/contact/access)
    - Assigning the 'user' role to some permissions and the 'user' user
      to other permissions, this will test both role and user based access rules.
- Login as user/user.
- Note: You might see "You have already submitted this form. View your previous submissions."
  because devel generated 'Contact' form submissions are randomly assigned to 
  existing users.
- Create a 'Contact' form submission (/yamlform/contact)
- Check CRUD operations on 'Contact' form (/yamlform/contact/submissions)
- Check navigating between own submissions.


--------------------------------------------------------------------------------

# Translation

**Notes**

- Use [Google Translate](https://translate.google.com/) to create test 
  translations
- [Multilingual Drupal 8](http://hojtsy.hu/multilingual-drupal8)
- [Drupal 8 multilingual tidbits 16: configuration translation development](http://hojtsy.hu/blog/2014-may-26/drupal-8-multilingual-tidbits-16-configuration-translation-development)

**Setup**

- Enable YAML form translation test module (/admin/extend)

**Check languages**

- Check that 'Spanish' is added to languages. (/admin/config/regional/language)
 
**Check YAML form settings translation**

- Check 'Spanish' translation (/admin/structure/yamlform/settings/translate/es/edit) 

**Check 'Contact' form translation**

- Check 'Contact' translations 
  (/admin/structure/yamlform/manage/contact/translate/)
- Check 'Contact' Spanish translations 
  (/admin/structure/yamlform/manage/contact/translate/es/edit)

**Check that the translated inputs can no be altered** 

_Once a YAML form is translated only the Inputs (YAML) values be changed._  
  
- Goto 'Contact' translations 
  (/admin/structure/yamlform/manage/contact/translate/)
    - Alter an element/property
    - Remove an element/property
    - Add an element/property    
   
- Goto 'Contact' Spanish translations 
  (/admin/structure/yamlform/manage/contact/translate/es/edit)
    - Alter an element/property
    - Remove an element/property
    - Add an element/property    
  
**Test 'Contact' form** 

- Test 'Contact' form submission via Spanish
  (/es/yamlform/contact/test)
