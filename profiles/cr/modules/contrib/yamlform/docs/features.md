Features
--------

### Form Builder

<div class="thumbnail">
<a href="http://thebigbluehouse.com/images/yamlform/features/form-builder.png">
<img src="http://thebigbluehouse.com/images-thumbnails/yamlform/features/form-builder.png" alt="Form Builder" />
</a>
</div>

The YAML Form module provides an intuitive form builder based upon Drupal 8's 
best practices for user interface and user experience. The form builder allows non-technical users to easily build and maintain forms.

Form builder features include:

- Drag-n-drop form element management
- Generation of test submissions
- Duplication of existing forms, templates, and elements


### Form Settings

<div class="thumbnail">
<a href="http://thebigbluehouse.com/images/yamlform/features/form-settings.png">
<img src="http://thebigbluehouse.com/images-thumbnails/yamlform/features/form-settings.png" alt="Form Settings" />
</a>
</div>

Form submission handling, messaging, and confirmations are completely 
customizable using global settings and/or form-specific settings.
 
Form settings that can be customized include:

- Messages and button labels
- Confirmation page, messages, and redirects
- Confidential submissions
- Prepopulating a form's elements using query string parameters
- Limiting number of submission per user, per form, and/or per node


### Form Elements

<div class="thumbnail">
<a href="http://thebigbluehouse.com/images/yamlform/features/form-elements.png">
<img src="http://thebigbluehouse.com/images-thumbnails/yamlform/features/form-elements.png" alt="Form Elements" />
</a>
</div>

The YAML Form module is built directly on top of Drupal 8's Form API. Every
[form element](https://api.drupal.org/api/drupal/developer!topics!forms_api_reference.html/8) 
available in Drupal 8 is supported by the YAML Form module.

Form elements include:

- **HTML:** Textfield, Textareas, Checkboxes, Radios, Select menu, 
  Password, and more...
- **HTML5:** Email, Url, Number, Telephone), Date, Number, Range, 
  and more...
- **Drupal specific** File uploads, Entity References, Table select, Date list, 
  and more...
- **Custom:** [Likert scale](https://en.wikipedia.org/wiki/Likert_scale), 
  Star rating, Toggle, Credit card number, Select/Checkboxes/Radios with other, 
  and more...
- **Composite elements:** Address, Contact, and Credit Card 

### Custom Properties

<div class="thumbnail">
<a href="http://thebigbluehouse.com/images/yamlform/features/custom-properties.png">
<img src="http://thebigbluehouse.com/images-thumbnails/yamlform/features/custom-properties.png" alt="Custom Properties" />
</a>
</div>

All of Drupal 8's default form element properties and behaviors are supported. 
There are also several custom form element properties and settings
available to enhance a form element's behavior.
 
Standard and custom properties allow for:

- **Conditional logic** using [FAPI States API](https://api.drupal.org/api/examples/form_example%21form_example_states.inc/function/form_example_states_form/7)
- **Input masks** (using [jquery.inputmask](https://github.com/RobinHerbots/jquery.inputmask))
- **[Select2](https://select2.github.io/)** replacement of select boxes 
- **Private** elements, visible only to administrators
- **Unique** values per element


### Viewing Source

<div class="thumbnail">
<a href="http://thebigbluehouse.com/images/yamlform/features/viewing-source.png">
<img src="http://thebigbluehouse.com/images-thumbnails/yamlform/features/viewing-source.png" alt="Viewing Source" />
</a>
</div>

At the heart of a YAML Form module's form elements is a Drupal render array,
which can be edited and managed by developers. The Drupal render array gives developers
complete control over a form's elements, layout, and look-and-feel by
allowing developers to make bulk updates to a form's label, descriptions, and 
behaviors.


### States/Conditional Logic

<div class="thumbnail">
<a href="http://thebigbluehouse.com/images/yamlform/features/states-conditional-logic.png">
<img src="http://thebigbluehouse.com/images-thumbnails/yamlform/features/states-conditional-logic.png" alt="States/Conditional Logic" />
</a>
</div>

Drupal's State API can be used by developers to provide conditional logic to 
hide and show form elements.

Drupal's State API supports:

- Show/Hide
- Open/Close
- Enable/Disable

### Multistep Forms

<div class="thumbnail">
<a href="http://thebigbluehouse.com/images/yamlform/features/multistep-forms.png">
<img src="http://thebigbluehouse.com/images-thumbnails/yamlform/features/multistep-forms.png" alt="Multistep Forms" />
</a>
</div>

Forms can be broken up into multiple pages using a progress bar. Authenticated
users can save drafts and/or have their changes automatically saved as they 
progress through a long form.

Multistep form features include:

- Customizable progress bar
- Customizable previous and next button labels
- Saving drafts between steps


### Email/Handlers

<div class="thumbnail">
<a href="http://thebigbluehouse.com/images/yamlform/features/email-handlers.png">
<img src="http://thebigbluehouse.com/images-thumbnails/yamlform/features/email-handlers.png" alt="Email/Handlers" />
</a>
</div>

Upon form submission, customizable email notifications and confirmations can
be sent to users and administrators. 

An extendable plugin that allows developers to push submitted data 
to external or internal systems and/or applications is provided. 

Email support features include:

- Previewing and resending emails
- Sending HTML emails
- File attachments (requires the [Mail System](https://www.drupal.org/project/mailsystem) and [Swift Mailer](https://www.drupal.org/project/swiftmailer) module.) 
- HTML and plain-text email-friendly Twig templates
- Customizable display formats for individual form elements


### Results Management

<div class="thumbnail">
<a href="http://thebigbluehouse.com/images/yamlform/features/results-management.png">
<img src="http://thebigbluehouse.com/images-thumbnails/yamlform/features/results-management.png" alt="Results Management" />
</a>
</div>

Form submissions can optionally be stored in the database, reviewed, and
downloaded.  

Submissions can also be flagged with administrative notes.

Results management features include:

- Flagging
- Administrative notes 
- Viewing submissions as HTML, plain text, and YAML
- Customizable reports
- Downloading results as a CSV to Google Sheets or MS Excel
- Saving of download preferences per form


### Access Controls

<div class="thumbnail">
<a href="http://thebigbluehouse.com/images/yamlform/features/access-controls.png">
<img src="http://thebigbluehouse.com/images-thumbnails/yamlform/features/access-controls.png" alt="Access Controls" />
</a>
</div>

The YAML Form module provide full access controls for managing who can create
forms, post submissions, and access a form's results.  
Access controls can be applied to roles and/or specific users.

Access controls allow users to:

- Create new forms
- Update forms
- Delete forms
- View submissions
- Update submissions
- Delete submissions


### Reusable Templates

<div class="thumbnail">
<a href="http://thebigbluehouse.com/images/yamlform/features/reusable-templates.png">
<img src="http://thebigbluehouse.com/images-thumbnails/yamlform/features/reusable-templates.png" alt="Reusable Templates" />
</a>
</div>

The YAML Form module provides a few starter templates and examples that form 
administrators can update or use to create new reusable templates for their 
organization.

Starter templates include:

- Contact form
- Registration form
- Job Application form 
- Subscribe form


### Reusable Options

<div class="thumbnail">
<a href="http://thebigbluehouse.com/images/yamlform/features/reusable-options.png">
<img src="http://thebigbluehouse.com/images-thumbnails/yamlform/features/reusable-options.png" alt="Reusable Options" />
</a>
</div>

Administrators can define reusable global options for select menus, checkboxes, 
and radio buttons. The YAML Form module includes default options for states,
countries, [likert](https://en.wikipedia.org/wiki/Likert_scale) answers, 
and more.   

Reusable options include:

- Country codes & names	
- State/province codes & names	
- State codes	& names		
- Likert agreement, comparison, importance, satisfaction, ten scale, and
  would you


### Internationalization
    
<div class="thumbnail">
<a href="http://thebigbluehouse.com/images/yamlform/features/internationalization.png">
<img src="http://thebigbluehouse.com/images-thumbnails/yamlform/features/internationalization.png" alt="Internationalization" />
</a>
</div>

Forms and configuration can be translated into multiple languages using Drupal's
configuration translation system.    


### Drupal Integration

<div class="thumbnail">
<a href="http://thebigbluehouse.com/images/yamlform/features/drupal-integration.png">
<img src="http://thebigbluehouse.com/images-thumbnails/yamlform/features/drupal-integration.png" alt="Drupal Integration" />
</a>
</div>

Forms can be attached to nodes or displayed as blocks.  Forms can also have 
dedicated SEO-friendly URLs. Form elements are simply render arrays that can
easily be altered using custom hooks and/or plugins.


### Drush Integration

Drush commands are provided to:

- Generate multiple form submissions.
- Export form submissions.
- Purge form submissions.
- Download and manage third party libraries.
- Tidy YAML configuration files. 

<!-- Creates the bootstrap modal where the image will appear -->
<div class="modal fade" id="modal-lightbox" tabindex="-1" role="dialog" aria-labelledby="modal-lightbox-label" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
        <h4 class="modal-title" id="modal-lightbox-label"></h4>
      </div>
      <div class="modal-body">
        <img class="img-responsive">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
