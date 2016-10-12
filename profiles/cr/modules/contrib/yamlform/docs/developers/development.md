Development Cheatsheet
----------------------

Below are useful commands and cheatsheets that make it a little easier for
me to maintain the YAML Form module.

### Patching

**[Create and manage patches](https://www.drupal.org/node/707484)**

```bash
# Create and checkout issue branch
git checkout -b [issue-number]-[issue-description]

# Push issue branch to D.O. (optional)
git push -u origin [issue-number]-[issue-description]

# Create patch by comparing (current) issue branch with 8.x-1.x branch 
git diff 8.x-1.x > [project_name]-[issue-description]-[issue-number]-[comment-number]-[drupal-version].patch
```

**Ignoring *.patch, *.diff, and .gitignore files**

```bash
cat >> .gitignore <<'EOF'
.gitignore
*.patch
*.diff
EOF
```
**[Apply patch](https://www.drupal.org/node/1399218)**

```bash
curl https://www.drupal.org/files/[patch-name].patch | git apply -
```

**[Revert patch](https://www.drupal.org/patch/reverse)**

```bash
curl https://www.drupal.org/files/[patch-name].patch | git apply -R -
```

### Branching

**Merge branch**

```bash
# Merge branch with all commits
git checkout 8.x-1.x
git merge [issue-number]-[issue-description]
git push

# Merge branch as a single new commit
git checkout 8.x-1.x
git merge --squash [issue-number]-[issue-description]
git commit -m 'Issue #[issue-number]: [issue-description]'
git push
```
**Exporting a branch**

```bash
# Create a zip archive for a branch
git archive --format zip --output yamlform-[issue-number]-[issue-description].zip [issue-number]-[issue-description]
```

**Delete issue branch**

```bash
# Delete local issue branch.
git branch -d [issue-number]-[issue-description] 

# Delete remote issue branch.
git push origin :[issue-number]-[issue-description]
```

### [Interdiff](https://www.drupal.org/documentation/git/interdiff)

```bash
interdiff \
  [issue-number]-[old-comment-number].patch \
  [issue-number]-[new-comment-number].patch \
  > interdiff-[issue-number]-[old-comment-number]-[new-comment-number].txt
```

### GitFlow

Below is a cheatsheet for using [GitFlow](https://www.drupal.org/node/2406727) 
for development. 

```bash
# Create branch
git checkout 8.x-1.x
git checkout -b [issue-number]-[issue-description]
git push -u origin [issue-number]-[issue-description]

# Create patch
git diff 8.x-1.x > [project_name]-[issue-description]-[issue-number]-00.patch

# Merge branch with all commits
git checkout 8.x-1.x
git merge [issue-number]-[issue-description]
git push

# Merge branch as a single new commit
git checkout 8.x-1.x
git merge --squash [issue-number]-[issue-description]
git commit -m 'Issue #[issue-number]: [issue-description]'
git push

# Delete branch
git branch -D [issue-number]-[issue-description]
git push origin :[issue-number]-[issue-description]
```

### Drush 

**Reinstall YAML Form module.**

```bash
drush php-eval 'module_load_include('install', 'yamlform'); yamlform_uninstall();'; drush cron;
drush php-eval 'module_load_include('install', 'yamlform_node'); yamlform_node_uninstall();'; drush cron; 
drush yamlform-purge --all -y; drush pmu -y yamlform_test; drush pmu -y yamlform_devel; drush pmu -y yamlform_examples; drush pmu -y yamlform_templates; drush pmu -y yamlform_ui; drush pmu -y yamlform_node; drush pmu -y yamlform; 
drush en -y yamlform yamlform_ui yamlform_devel yamlform_examples yamlform_templates yamlform_node;

# Optional.
drush en -y yamlform_test;
drush en -y yamlform_test_third_party_settings;
drush en -y yamlform_translation_test;
drush pmu -y yamlform_test_third_party_settings yamlform_translation_test;
```

**Reinstall YAML Form Test module.**

```bash
drush yamlform-purge --all -y; drush pmu -y yamlform_test; drush en -y yamlform_test;
```

**Manage YAML Form module configuration using the [Features](https://www.drupal.org/project/features) module**

```
# Make sure all modules that are going to be exported are enabled
drush en -y yamlform yamlform_examples yamlform_templates yamlform_test yamlform_node;

# Show the difference between the active config and the default config.
drush features-diff yamlform
drush features-diff yamlform_test

# Export form configuration from your site.          
drush features-export -y yamlform
drush features-export -y yamlform_test
drush features-export -y yamlform_examples
drush features-export -y yamlform_templates
drush features-export -y yamlform_node

# Tidy form configuration from your site.          
drush yamlform-tidy -y yamlform
drush yamlform-tidy -y yamlform_test
drush yamlform-tidy -y yamlform_examples
drush yamlform-tidy -y yamlform_templates
drush yamlform-tidy -y yamlform_node

# Re-import all form configuration into your site.      
drush features-import -y yamlform
drush features-import -y yamlform_test
drush features-import -y yamlform_examples
drush features-import -y yamlform_templates
drush features-import -y yamlform_node
```

**Install extra modules.**

```bash
drush en -y yamlform captcha image_captcha honeypot validators;
```

**Create test roles and users.**

```bash
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

drush role-create viewer
drush role-add-perm viewer 'view the administration theme,access toolbar,access administration pages,access content overview,access yamlform overview,view any yamlform submission'
drush user-create viewer --password="viewer"
drush user-add-role viewer viewer

drush role-create user
drush user-create user --password="user"
drush user-add-role user user

drush role-create any
drush user-create any --password="any"
drush role-add-perm any 'view the administration theme,access administration pages,access toolbar,access yamlform overview,create yamlform,edit any yamlform,delete any yamlform,view yamlform submissions any node,edit yamlform submissions any node,delete yamlform submissions any node'
drush user-add-role any any

drush role-create own
drush user-create own --password="own"
drush role-add-perm own 'view the administration theme,access administration pages,access toolbar,access yamlform overview,create yamlform,edit own yamlform,delete own yamlform,view yamlform submissions own node,edit yamlform submissions own node,delete yamlform submissions own node'
drush user-add-role own own
```

**Create test submissions for 'Contact' and 'Example: Elements' form.**

```bash
drush yamlform-generate contact
drush yamlform-generate example_elements
```

**Test update hooks**

```bash
drush php-eval 'module_load_include('install', 'yamlform'); ($message = yamlform_update_8001()) ? drupal_set_message($message) : NULL;'
```

**Access developer information**

```bash
drush role-add-perm anonymous 'access devel information'
drush role-add-perm authenticated 'access devel information'
```

**Reinstall**

```bash 
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
  yamlform_devel\
  yamlform_examples\
  yamlform_node\
  yamlform_templates\
  yamlform_test\
  yamlform_translation_test;
```
