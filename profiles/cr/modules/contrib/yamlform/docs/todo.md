# Todo

- Review caching strategy, including tags and contexts.

# Tests

- Additional test coverage
- Improve test performance
- Implement more PHPUnit and KernelTestBase tests
- Input specific tests including Entity autocomplete, managed file, dates.
- Add test method to element plugin
    - Managed file
    - Entity autocomplete

- Missing Tests
    - \Drupal\yamlform\Controller\YamlFormOptionsController::autocomplete
    - src/Plugin/Field
        - \Drupal\yamlform\Plugin\Field\FieldFormatter\YamlFormEntityReferenceEntityFormatter
        - \Drupal\yamlform\Plugin\Field\FieldType\YamlFormEntityReferenceItem
        - \Drupal\yamlform\Plugin\Field\FieldWidget\EntityReferenceAutocompleteWidget
    
# Questions

- Should submitted values be casted to datatypes?  
  For example, should number inputs be casted to integers.

- Should empty values be hidden by default?

- Should we support private file uploads?  

- How should handlers deal with their global settings?

- Is the YamlForm entity doing too much? 
