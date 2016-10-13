Documentation README
--------------------

### How to take screenshot

**Setup**

- Use Chrome.
- Resize browser to 1024px using [Web  Developer](http://chrispederick.com/work/web-developer/)
- Use [Awesome Screenshot](http://www.awesomescreenshot.com/)
- Take full page screenshot
- Use 'feature name' as the file name.
- Save to 'yamlform-features' directory.

**Required screenshots**

    form-builder
    form-settings
    form-elements
    custom-properties
    viewing-source
    states-conditional-logic
    multistep-forms
    email-handlers
    submission-limits
    results-management
    access-controls
    reusable-templates
    reusable-options
    internationalization
    drupal-integration
    drush-integration
    

### How to take screencast

**Setup**

- Drupal
    - `osx install-site d8_dev`
    - Remove all blocks in first sidebar.  
      http://localhost/d8_dev/admin/structure/block
- Desktop
    - Switch to laptop.
    - Turn 'Hiding on'
    - Set screen display to 'Large Text'
- Chrome
    - Hide Bookmarks
    - Hide Extra Icons
    - Always Show Toolbar in Full Screen
    - Delete all yamlform.* keys from local storage.

**Generate list of screencasts**

    $help = _yamlform_help();
    print '<pre>';
    foreach ($help as $name => $info) {
      print "yamlform-" . $name . "\n";
      print 'YAML Form Help: ' . $info['title'] . "\n";
      print "\n";
    }
    print '</pre>'; exit;
  
**Uploading**

- Title : YAML Form Help: {title} [v01]
- Tags: Drupal 8,YAML Form,Form Builder
- Privacy: Unlisted
