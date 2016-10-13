Manual Test Script
------------------

This is a step-by-step guide for manually testing and reviewing every feature,
role/permission, and use case addressed by the YAML Form module.

### Notes

- Contact form will be used for most examples.
- Incognito tab is the quickest way to test anonymous access to a form/feature.

--------------------------------------------------------------------------------

### Installation

- Update settings.php to allow the yamlform_test.module to be installed.  
  `$settings['extension_discovery_scan_tests'] = TRUE;`
- Enable the form and form test module.

--------------------------------------------------------------------------------

### Setup 

See: development-tools.md for commands.

- Create test roles and users.
- Create test submissions.


--------------------------------------------------------------------------------

### Administration

**Forms (/admin/structure/yamlform)**

- Review forms provided by the yamlform.module and yamlform_test.module
- Test filter
    - Filter a title, description, and/or elements (which is not visible)
- Test sorting
- Elements validation
    - Required: Leave blank
    - Valid YAML: Enter invalid YAML
    - Is array: Enter simple string value
    - Duplicate elements name: Enter two elements with the same names
    - Ignore properties: Enter element with #tree, #submit, etc... properties.
        
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

**Form options (/admin/structure/yamlform/settings/options/manage)**

- Review default form options
- Create, update, and delete form options

**Form elements (/admin/structure/yamlform/settings/elements)**

- Review form element titles and descriptions.

**Form handlers (/admin/structure/yamlform/settings/handlers)**

- Review form handler titles and descriptions.


--------------------------------------------------------------------------------

### Forms

**(/admin/structure/yamlform)**

**Create new form (/admin/structure/yamlform/add)**

- Check default elements
- Test duplicate link
- Message about previous submissions should be displayed

**Duplicate existing form (/admin/structure/yamlform/manage/template_registration/duplicate)**

- Duplicate a 'Template: Registration' form.
- Confirm all elements and settings are duplicated.

**Test form elements (/yamlform/example_elements/test)**

- Test 'kitchen sink' list of supported elements
- Verify each element collects and displays submitted data correctly

**Test form elements with custom elements (/yamlform/example_elements_formats/test)**

- Test elements have customized formats
- Verify HTML and text display for submitted data

**Form (/yamlform/contact)**

- Check system path (/yamlform/contact)
- Check submit alias (/form/contact)
- Check confirmation alias (/form/contact/confirmation)
     - This page is not used by the form which redirects to the homepage.

**Form settings (/admin/structure/yamlform/manage/contact/settings)**

- Review form
- Review hide/show logic (ie #States API).
- Review default values
- Review help text

**Form access (/admin/structure/yamlform/manage/contact/access)**

See: Access Rules 

**Form handlers (/admin/structure/yamlform/manage/contact/handlers)**

- Test contact form handlers
- Create, update, and delete form handler
- Check that cardinality is support
    - Email handler support multiple instances
    - Null and Test handler only support a single instance
- Test disabling a handler

**Form email handlers (/admin/structure/yamlform/manage/contact/handlers)**

- Test email handlers
- Test debug setting


--------------------------------------------------------------------------------

### Wizard

**Form wizard** (/form/example-wizard)

- Check progress bar
- Check data automatically saved between pages
- Check reloading form returns to the current page
- Review preview
- Review confirmation
- Check default wizard previous and next button labels 
  (/admin/structure/yamlform/settings)
- Check form previous and next button labels 
  (/admin/structure/yamlform/manage/example_wizard/settings)
- Check adding `#previous_button_label` and `#next_button_label` to 
  `wizard_page` (/admin/structure/yamlform/manage/example_wizard)


--------------------------------------------------------------------------------

### Third Party Settings

**Form global third party settings** (/admin/structure/yamlform/manage/contact/third-party)

- Check no modules installed message
- Enable yamlform_test_third_party_settings.module
- Add global message
- Check global message is display on Contact form (/yamlform/contact)

**Form specific third party settings** (/admin/structure/yamlform/manage/contact/third-party)

- Check no modules installed message
- Enable yamlform_test_third_party_settings.module
- Add message
- Check form specific message is display on Contact form (/yamlform/contact)


--------------------------------------------------------------------------------

### Results

**Submissions (/admin/structure/yamlform/manage/contact/results/submissions)**

- Check submission columns
- Check sorting
- Test filter
    - Filter by submission data (which is not visible)

**Table (/admin/structure/yamlform/manage/contact/results/table)**

- Check elements columns (which are not sortable)
- Check 'Example: Elements'
     - /admin/structure/yamlform/manage/example_elements/results/table

**Download (/admin/structure/yamlform/manage/contact/results/download)**

- Unchecking 'Download CSV' allows you to view the CSV as plain text.
     - 'Download CSV' is only available when there is less than 1000 submissions.
- Check generating CSV using batch processing
     - Set batch limit to 1. (/admin/structure/yamlform/settings)
     - Download CSV (/admin/structure/yamlform/manage/contact/results/download)
     
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

### Features

**Elements**

- All elements (/yamlform/test_element)
- Date elements (/yamlform/test_element_dates)
- Entity autocomplete (/yamlform/test_element_entity_reference)
    - Preview is broken and throwing serialization error.
- Text format (/yamlform/test_element_text_format)
- Ignored properties (/yamlform/test_element_ignored_properties)

**Closed (/yamlform/test_form_closed)**

- Check that form is closed for anonymous user
- Check that form is available to admin user but displays are warning.

**Prepopulate (/yamlform/test_form_prepopulate)**

- Check that name is prepopulated using query string variable.

**Confidential submissions (/yamlform/test_form_confidential)**

- Check that form can only be accessed when logged out.
- Check that IP address is not saved with submission.

**Submit text (/yamlform/test_form_submit_text)**

- Check that submit text is customized.

**Preview (/yamlform/test_form_preview)**

- Check custom preview and next submission buttons.
- Check custom preview message.
- Check optional and required preview.

**Draft (/yamlform/test_form_draft)**

- Check saving and reloading draft
- Check autosave occurs with validation errors.
- Check autosave occurs when previewing.

**Confirmation**

- Inline (/yamlform/test_confirmation_inline)
- Message (/yamlform/test_confirmation_message)
- Page (/yamlform/test_confirmation_page)
- URL (/yamlform/test_confirmation_url)

**Limits (/yamlform/test_submission_limit)**

- Check only 1 submission is allowed for authenticated user.
- Check only 3 submission are allowed for all users.
- Check that admin can post new submission if their limit has no been reached.

**Results Disabled (/yamlform/test_submission_disabled)**

- Check that results can be disabled. (Applies to users)

**Private element access (/yamlform/test_element_private)**

- Check that element with \#private property is only accessible to submission administrators.

**Unique element property (/yamlform/test_element_unique)**

- Check that element with \#unique property can't have same value submitted twice.
- Check that existing submission can be updated. 

--------------------------------------------------------------------------------

### Permissions

- Create test roles and users. (See development.md)

**Account/Roles**

- developer: Administer forms and form submissions.
- admin: Administer form submissions.
- manager: Used to test managing a form's submissions.
- user: Used to test accessing and managing one's own submission.
- any: Manage any form and form node submissions.
- own: Manage own form and form node submissions.

**Create own form**

- Login as own/own.
- Check that 'Form overview' is accessible. (/admin/structure/yamlform)
- Check add form (/admin/structure/yamlform/add)
  - Call new form 'own'
- Check adding element (/admin/structure/yamlform/manage/own)
- Check duplicating 'own' form.
  - Call new form 'duplicate'
- Check deleting 'duplicate' form (/admin/structure/yamlform/manage/duplicate/delete)
- Check that 'Templates' are available and can be previewed. (/admin/structure/yamlform/templates)

**Manage own form**

- Create test form submission on 'own' form (/yamlform/own/test)
- Check that submission is accessible and editable.

**Managing any form**

- Login as any/any.
- Check that all forms and results are available. (/admin/structure/yamlform)
- Check that form global settings are hidden.
- Check add form (/admin/structure/yamlform/add)
  - Call new form 'any'

**Changing form author**

- Login as developer/developer.
- Change form author from 'any' to 'own' at the very bottom of form's 
  settings. (/admin/structure/yamlform/any/settings)
- Login as own/own.
- Check that 'any' form is accessible. (/admin/structure/yamlform/any)

**Duplicating form**

- Login as own/own.
- Check that 'own' user can't duplicate a form that they can't update. 
  (/admin/structure/yamlform/manage/contact/duplicate)
      - Returns 'Access denied'.


--------------------------------------------------------------------------------

### Access Rules

- Create test roles and users. (See development.md)

**Account/Roles**

- developer: Administer forms and form submissions.
- admin: Administer form submissions.
- manager: Used to test managing a form's submissions.
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

### Translation

**Notes**

- Use [Google Translate](https://translate.google.com/) to create test 
  translations
- [Multilingual Drupal 8](http://hojtsy.hu/multilingual-drupal8)
- [Drupal 8 multilingual tidbits 16: configuration translation development](http://hojtsy.hu/blog/2014-may-26/drupal-8-multilingual-tidbits-16-configuration-translation-development)

**Setup**

- Enable form translation test module (/admin/extend)

**Check languages**

- Check that 'Spanish' is added to languages. (/admin/config/regional/language)
 
**Check form settings translation**

- Check 'Spanish' translation (/admin/structure/yamlform/settings/translate/es/edit) 

**Check 'Contact' form translation**

- Check 'Contact' translations 
  (/admin/structure/yamlform/manage/contact/translate/)
- Check 'Contact' Spanish translations 
  (/admin/structure/yamlform/manage/contact/translate/es/edit)

**Check that the translated elements can no be altered** 

_Once a form is translated only the Elements (YAML) values be changed._  
  
- Go to 'Contact' translations 
  (/admin/structure/yamlform/manage/contact/translate/)
    - Alter an element/property
    - Remove an element/property
    - Add an element/property    
   
- Go to 'Contact' Spanish translations 
  (/admin/structure/yamlform/manage/contact/translate/es/edit)
    - Alter an element/property
    - Remove an element/property
    - Add an element/property    
  
**Test 'Contact' form** 

- Test 'Contact' form submission via Spanish
  (/es/yamlform/contact/test)

--------------------------------------------------------------------------------

### User Interface

**Setup**

- Enable YAML Form UI module (/admin/extend)

**Reorder Basic Form**

- Go to 'Contact' 
  (/admin/structure/yamlform/manage/contact)
- Check UI displayed in 'Elements' tabs
- Check YAML is display in 'Source (YAML)' tab.
- Check drag-n-drop reordering.
- Check 'Show row weights'
    - Parent is readonly

**Reorder Advanced Form**

- Go to 'Example: Elements' 
  (/admin/structure/yamlform/manage/example_elements)
- Check moving elements under containers.

**Reorder Wizard Form**

- Go to 'Example: Wizard' 
  (/admin/structure/yamlform/manage/example_wizard)
- Check that 'Wizard page' cannot be nested and stays as root element.

**Create, Update, and Delete Element**

- Go to 'Contact' 
  (/admin/structure/yamlform/manage/contact)
- Add element (/admin/structure/yamlform/manage/contact/element/add)
- Edit element
- Delete element

**Duplicate Element**

- Go to 'Contact' 
  (/admin/structure/yamlform/manage/contact)
- Duplicate 'Name' element (/admin/structure/yamlform/manage/contact/element/name/duplicate)

**Create, Update, and Delete Container**

- Go to 'Contact' 
  (/admin/structure/yamlform/manage/contact)
- Add container element (/admin/structure/yamlform/manage/contact/element/add)
- Add element to container
- Delete container

**Test Elements**

- Enable form test module (/admin/extend)

- Go to 'Elements'
  (/d8_dev/admin/structure/yamlform/settings/elements)

- Click 'Test' under 'Operations' for each element.

--------------------------------------------------------------------------------

### Node Integration

**Setup**

- Enable form node module (/admin/extend)
- Create test 'any' and 'own' roles and users. (See development.md)

**Account/Roles**

- any: Manage any form and form node submissions.
- own: Manage own form and form node submissions.

**Create form node**

- Go to 'Create form' 
  (node/add/yamlform)
- Enter custom title
- Select 'Contact' form
- Save and publish
- Check 'Contact' form is displayed when node is viewed.
- Check 'Test' tab.
- Check 'Results' tab.
  
**Submission (/node/{node}/yamlform/results/submissions)**

- Check only form node submissions are displayed.
- Check only form node submissions can be navigated to.
- Check that breadcrumbs link to the form node.
- Check that only submissions associated with the current node can be accessed.
    - Must manually enter a different sid in the URL.
      (/node/{node}/yamlform/results/manage/{yamlform_submission})

**Access any form node submissions**

- Login as any:any.
- Check 'Test' tab.
- Check 'Results' tab.

**Access own form node submissions**

- Change form node author to 'own'.
- Login as own:own.
- Check 'Test' tab.
- Check 'Results' tab.

**Draft and Saved**

- Enable drafts for 'Contact' form.
  (/admin/structure/yamlform/manage/contact/settings)
- Save draft for 'Contact' form.
  (/yamlform/contact)
- Save draft for form node referencing 'Contact' form.
  (/node/{node})
- Check that the correct draft is loaded for both form instances.
- Check that previously saved submission is correctly linked to for both YAML
  form instances.
