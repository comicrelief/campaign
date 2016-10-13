
Steps for creating a new release
--------------------------------

  1. Cleanup code
  2. Review code
  3. Run tests
  4. Generate release notes
  5. Tag and create a new release
  6. Update project page
  7. Update documentation


1. Cleanup code
---------------

[Convert to short array syntax](https://www.drupal.org/project/short_array_syntax)

    drush short-array-syntax yamlform

Tidy YAML files

    drush yamlform-tidy yamlform; 
    drush yamlform-tidy yamlform_ui; 
    drush yamlform-tidy yamlform_test;
    drush yamlform-tidy yamlform_translation_test;


2. Review code
--------------

[Online](http://pareview.sh)

    http://git.drupal.org/project/yamlform.git 8.x-1.x

[Commandline](https://www.drupal.org/node/1587138)

    # Make sure to remove the node_modules directory.
    rm -Rf node_modules

    # Check Drupal coding standards
    phpcs --standard=Drupal --extensions=php,module,inc,install,test,profile,theme,css,info modules/sandbox/yamlform
    
    # Check Drupal best practices
    phpcs --standard=DrupalPractice --extensions=php,module,inc,install,test,profile,theme,js,css,info modules/sandbox/yamlform


3. Run tests
------------

[SimpleTest](https://www.drupal.org/node/645286)

    # Run all tests
    php core/scripts/run-tests.sh --url http://localhost/d8_dev --module yamlform

[PHPUnit](https://www.drupal.org/node/2116263)

    # Execute all PHPUnit tests.
    cd core
    php ../vendor/phpunit/phpunit/phpunit --group YamlFormUnit

    # Execute individual PHPUnit tests.
    cd core
    export SIMPLETEST_DB=mysql://drupal_d8_dev:drupal.@dm1n@localhost/drupal_d8_dev;
    php ../vendor/phpunit/phpunit/phpunit ../modules/sandbox/yamlform/tests/src/Unit/YamlFormTidyTest.php
    php ../vendor/phpunit/phpunit/phpunit ../modules/sandbox/yamlform/tests/src/Unit/YamlFormHelperTest.php
    php ../vendor/phpunit/phpunit/phpunit ../modules/sandbox/yamlform/tests/src/Unit/YamlFormElementHelperTest.php
    php ../vendor/phpunit/phpunit/phpunit ../modules/sandbox/yamlform/tests/src/Unit/YamlFormOptionsHelperTest.php
    php ../vendor/phpunit/phpunit/phpunit ../modules/sandbox/yamlform/tests/src/Unit/YamlFormArrayHelperTest.php     
    php ../vendor/phpunit/phpunit/phpunit ../modules/sandbox/yamlform/src/Tests/YamlFormEntityElementsValidationUnitTest.php    


4. Generate release notes
-------------------------

[Git Release Notes for Drush](https://www.drupal.org/project/grn)

    drush release-notes --nouser 8.x-1.0-VERSION 8.x-1.x


5. Tag and create a new release
-------------------------------

[Tag a release](https://www.drupal.org/node/1066342)

    git tag 8.x-1.0-VERSION
    git push --tags
    git push origin tag 8.x-1.0-VERSION

[Create new release](https://www.drupal.org/node/add/project-release/2640714)


6. Update project page
----------------------

[Export README](https://www.drupal.org/project/readme)
    
     # Update project page
     drush readme-export --project --path='docs/index.md' yamlform
     open https://www.drupal.org/node/2640714/edit
     
[Edit project page](https://www.drupal.org/node/2640714/edit)


7. Update documentation
-----------------------

[Update Roadmap](http://yamlform.io/developers/roadmap/)

     npm install; grunt docs-deploy;
