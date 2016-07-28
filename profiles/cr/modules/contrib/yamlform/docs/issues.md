[Unable to tidy the bulk export of YamlForm and YamlFormOptions config files 
because Drupal's YAML utility is not a service.](https://www.drupal.org/node/1920902)

---
      
[Regression: Form throws LogicException when trying to render a form with 
object as an element's default value.](https://www.drupal.org/node/2502195)  

- Impacts previewing entity autocomplete inputs.

---

[Access control is not applied to config entity queries](https://www.drupal.org/node/2636066)

  - YAML forms that the current user can't access are not being hidden via the EntityQuery.
  - See: Drupal\yamlform\YamlFormEntityListBuilder

---

Config entity does NOT support [Entity Validation API](https://www.drupal.org/node/2015613).

  - Validation constraints are only applicable to content entities and fields.
  - In D8 all config entity validation is handled via 
    \Drupal\Core\Form\FormInterface::validateForm
  - Workaround: Create YamlFormEntityInputsValidator service.      
    
---

[Forms System Issues for Drupal core](https://www.drupal.org/project/issues/drupal?status=Open&version=8.x&component=forms+system)
    
- [Allow FAPI usage of the datalist element](https://www.drupal.org/node/1593964)
- [Multistep Form Wizard](https://www.drupal.org/node/1886616) 
- [Implement Form API support for new HTML5 elements](https://www.drupal.org/node/1183606)   

