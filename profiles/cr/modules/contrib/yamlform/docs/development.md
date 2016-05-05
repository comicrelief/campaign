# Commands

## [Update composer libraries](https://www.drupal.org/project/composer_manager)

    composer drupal-update

## [Apply patch](https://www.drupal.org/node/1399218)

    curl https://www.drupal.org/files/[patch-name].patch | git apply -

## [Create and manage patches](https://www.drupal.org/node/707484)

    # Create and checkout issue branch
    git checkout -b [issue-number]-[issue-description]
    
    # Push issue branch to D.O. (optional)
    git push -u origin [issue-number]-[issue-description]
    
    # Create patch by comparing (current) issue branch with  8.x-1.x branch 
    git diff 8.x-1.x > [project_name]-[issue-description]-[issue-number]-[comment-number]-[drupal-version].patch

## Ignoring patches (and .gitignore)

    cat >> .gitignore <<'EOF'
    .gitignore
    *.patch
    EOF
    
## Delete issue branch
 
     # Delete local issue branch.
     git branch -d [issue-number]-[issue-description] 

     # Delete remote issue branch.
     git push origin :[issue-number]-[issue-description]

## Cheatsheet
    
    # Create branch
    git checkout -b [issue-number]-[issue-description]
    git push -u origin [issue-number]-[issue-description]
    
    # Create patch
    git diff 8.x-1.x > [project_name]-[issue-description]-[issue-number]-00.patch

    # Delete branch
    git branch -d [issue-number]-[issue-description]
    git push origin :[issue-number]-[issue-description]

## Reinstall YAML form module.

    drush yamlform-purge --all -y; drush pmu -y yamlform_test yamlform_test_third_party_settings; drush pmu -y  yamlform; drush en -y yamlform; drush en -y yamlform_test;

    drush php-eval 'module_load_include('install', 'yamlform'); yamlform_uninstall();'
    drush cron;
    drush yamlform-purge --all -y; drush pmu -y yamlform_test yamlform_test_third_party_settings; drush pmu -y  yamlform; 
    drush en -y yamlform; drush en -y yamlform_test;

    # Optional.
    drush en -y yamlform_test_third_party_settings;

## Reinstall YAML form test module.

    drush yamlform-purge --all -y; drush pmu -y yamlform_test; drush en -y yamlform_test;

## Install extra modules.

    drush en -y yamlform captcha honeypot select_or_other;

## Create test roles and users.

    drush role-create developer
    drush role-add-perm developer 'view the administration theme,access toolbar,access administration pages,access content overview,access yamlform overview,administer yamlform,administer blocks,administer nodes'
    drush user-create developer --password="developer"
    drush user-add-role developer developer
    
    drush role-create admin
    drush role-add-perm admin 'view the administration theme,access toolbar,access administration pages,access content overview,access yamlform overview,administer yamlform submission'
    drush user-create admin --password="admin"
    drush user-add-role admin admin

    drush role-create manager
    drush role-add-perm manager 'view the administration theme,access toolbar,access administration pages,access content overview,access yamlform overview'
    drush user-create manager --password="manager"
    drush user-add-role manager manager

    drush role-create user
    drush user-create user --password="user"
    drush user-add-role user user

## Create test submissions for 'Contact' and 'Example: Inputs' form.

    drush yamlform-generate contact
    drush yamlform-generate example_inputs

## Test update hooks

    drush php-eval 'module_load_include('install', 'yamlform'); ($message = yamlform_update_8001()) ? drupal_set_message($message) : NULL;'
    
## Access developer information

    drush role-add-perm anonymous 'access devel information'
    drush role-add-perm authenticated 'access devel information'

## Update composer packages

    composer drupal-update

## Reinstall

    drush -y site-install\
      --account-mail="example@example.com"\
      --account-name="webmaster"\
      --account-pass="drupal.admin"\
      --site-mail="example@example.com"\
      --site-name="Drupal 8 (dev)";

    # Enable core modules
    drush -y pm-enable\
      book\
      simpletest\
      telephone\
      language\
      locale\
      content_translation\
      config_translation;
  
    # Disable core modules
    drush -y pm-uninstall\
      update;
  
    # Enable contrib modules
    drush -y pm-enable\
      devel\
      devel_generate\
      kint\
      webprofiler\
      yamlform\
      yamlform_test\
      yamlform_translation_test;

# Code Sources and Design Patterns

Below is just a reference to where most of the code snippets and/or 
design pattern were taken from to build this module.
 
- UI/Naming convention: Webform module (Copied)
- YamlForm to YamlFormSubmission bundle: Vocabulary to Term entities
- YamlFormSubmission entity_type and entity_id: Comment entity
- YamlFormSubmission preview: CommentForm
- YamlFormHandler plugin: ImageEffect
- YamlFormEntityReferenceItem field type: FileItem
