Notes and references for maintainers of the YAML form module.

# References

- [Create a project (module or theme) on Drupal.org](https://www.drupal.org/contribute-projects)


# Coding Standards

The YAML form module follows all [coding standards](https://www.drupal.org/coding-standards) 
established by the Drupal community.

Below are all additional standards that should be followed.

- [[Policy, no patch] PHP 5.4 short array syntax coding standards](https://www.drupal.org/node/2135291)  
  The release.md document includes command to convert all array to use the
  shorthand syntax.


# Becoming a co-maintainer

Everyone is welcome to contribute to the YAML form module by posting questions,
issues, feature requests, and patches to the YAML form module's [issue queue](https://www.drupal.org/project/issues/yamlform).
For the YAML form module to succeed, it needs multiple maintainers to ensure 
that it is maintainable. 

Pleases read [Best practices for co-maintaining projects](https://www.drupal.org/node/363367)
for how to become a co-maintainer of YAML form module.

Below are some specific tips and tasks for becoming a co-maintainer of the 
YAML form module

- Review and edit the [test script](test.md) which documents every 
  feature and function currently provide by the YAML form module.  

- Post issues and patches to help improve and/or fix this issues with the
  test script.
   
- Improving documentation and reviewing the YAML form module's APIs are two key
  tasks to ensuring that the YAML form module is maintainable.

- Review, improve, and write self documenting SimpleTests and PHPUnit tests.
  
- Write a contrib module that extends the YAML form module.
 
 
# Code Sources and Design Patterns

Below is where most of the code snippets and/or design pattern were taken from 
to build this module.
 
- UI/Naming convention: Webform module (Copied)
- YamlForm to YamlFormSubmission bundle: Vocabulary to Term entities
- YamlFormSubmission entity_type and entity_id: Comment entity
- YamlFormSubmission preview: CommentForm
- YamlFormHandler plugin: ImageEffect 
