Below is the current roadmap for the YAML Form module.

★ Indicates areas that I need help with. 

Phase I (before Release Candidate)
----------------------------------

### Design & UX 

**Templating ★**

- [#2787117](https://www.drupal.org/node/2787117)
  Add more starter templates ★
- Review out-of-the-box templates provide by the yamlform_templates.module. ★ 
  
**Code Review**

- Testability
- Refactorability
- Plugin definitions ★
- Entity API implementation ★
- Form API implementation ★

**API Review**

- Review doc blocks

**Libraries**

- Add external libraries to composer.json ★

**Testing**

- Refactor PHPUnit tests
- Improve SimpleTest setUp performance.
- Configuration Management
- Default configuration
- Finalize default admin settings

### Multilingual 

- Finalize how form's elements are translated. ★
- Make sure the YAML Form module is completely translatable. ★

### Documentation & Help 

**General**

- Decide if documentation should live on Drupal.org
- [#2759591](https://www.drupal.org/node/2759591)
  What is YAML and why we are using it? **POSTPONED**

**Module**

- Review hardcoded messages.

**Editorial ★**

- Unified tone
- General typos, grammar, and wording. ★


Phase II (after Stable Release)
-------------------------------

**Forms**

- [#2757491](https://www.drupal.org/node/2757491) 
  AJAX support for forms ★ 

**Rules/Actions**

- [#2779461](https://www.drupal.org/node/2779461) 
  Rules/Action integration ★

**Results**

- Create trash bin for deleted results.   
  _Copy D8 core's solutions_ 

**Views**

- [#2769977](https://www.drupal.org/node/2769977) 
  Views integration ★

**APIs** 

- REST API endpoint for CRUD operations.
- Headless Drupal Forms

**Other** 

- Code snippets repository
- Template repository
