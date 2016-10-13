Extending
---------

The YAML Form module provides [plugins](https://www.drupal.org/developing/api/8/plugins)
and hooks that allow contrib and custom modules to extend and enhance form 
elements and submission handling.

### YamlFormElement plugin

The YamlFormElement plugin is used to integrate and enhance form elements so 
that they can be properly integrated into the YAML Form module. For example,
to properly validate and handle file uploads using the 'Managed file' element, 
the YAML Form module provides a YamlFormElement plugin called 
[ManagedFile](http://cgit.drupalcode.org/yamlform/tree/src/Plugin/YamlFormElement/ManagedFile.php), 
which saves the uploaded files to a YAML Form specific file directory.


### YamlFormHandler plugin

The YamlFormHandler plugin allows developers to extend a form's submission 
handling. Each YamlFormHandler plugin should live in a dedicated 
module and handler namespace. For example, if a developer wanted to setup 
MailChimp integration, they would create the 'YAML Form MailChimp' module 
(yamlform_mailchimp.module), which would contain the YamlFormMailChimpHandler.

This approach/pattern allows popular YamlFormHandler plugins
(that include tests) to be easily contributed back to the main YAML Form module.


### Hooks

See [API documentation](http://cgit.drupalcode.org/yamlform/tree/yamlform.api.php).


### Third Party Settings

The YAML Form module also allows contrib modules to define third party settings
for all forms and/or for one specific form.  

See the [yamlform\_test\_third_party\_settings.module](http://cgit.drupalcode.org/yamlform/tree/tests/modules/yamlform_test_third_party_settings)
for an example of how a contrib module can use third party settings to extend 
and enhance the YAML Form module.
