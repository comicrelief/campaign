<?php

/**
 * @file
 * Generate examples of all elements using HTML escape.
 */

module_load_include('inc', 'yamlform_test', 'includes/yamlform_test.example_elements');

/**
 * Generate examples of all elements using HTML escape.
 *
 * @return array
 *   An array containing examples of all elements using HTML escaping.
 */
function yamlform_test_test_element_html_escape() {
  $elements = yamlform_test_example_elements();
  _yamlform_test_test_element_html_escape_recursive($elements);
  return $elements;
}

/**
 * Add script tag to elements.
 *
 * @param array $elements
 *   An array of elements.
 */
function _yamlform_test_test_element_html_escape_recursive(array &$elements) {
  foreach ($elements as $key => &$property) {
    if (in_array($key, ['#title', '#field_prefix', '#field_suffix'])) {
      $property = $property . ' | <script>alert(\'This markup is not escaped properly!!!\') </script>';
    }

    if (is_array($property)) {
      _yamlform_test_test_element_html_escape_recursive($property);
    }
  }
}
