<?php

/**
 * @file
 * Generate test elements with Flexbox wrappers.
 */

use Drupal\Component\Serialization\Yaml;

/**
 * Generate test elements with Flexbox wrappers.
 *
 * @return array
 *   An array containing test elements with Flexbox wrappers.
 */
function yamlform_test_test_element_flexbox() {
  // Track issues related to elements #states API.
  $issues = _yamlform_test_issues();

  // Elements to be ignored.
  $skipped_elements = [
    'hidden',
    'yamlform_flexbox',
    'yamlform_test',
    'yamlform_wizard_page',
  ];

  // Some tests don't install the filter.module so we should skip elements
  // that is depend on the filter.module.
  if (!\Drupal::moduleHandler()->moduleExists('filter')) {
    $skipped_elements[] = 'processed_text';
    $skipped_elements[] = 'text_format';
  }

  // Default element properties.
  $yaml = file_get_contents(drupal_get_path('module', 'yamlform_test') . '/includes/yamlform_test.example_elements.yml');
  $default_elements = Yaml::decode($yaml);

  $data = [
    'containers' => [],
    'elements' => [],
  ];

  /** @var \Drupal\yamlform\YamlFormElementManagerInterface $element_manager */
  $element_manager = \Drupal::service('plugin.manager.yamlform.element');
  $elements = $element_manager->getInstances();
  foreach ($elements as $element_type => $yamlform_element) {
    if (in_array($element_type, $skipped_elements)) {
      continue;
    }

    $element = [
      '#type' => $yamlform_element->getPluginId(),
      '#title' => (string) $yamlform_element->getPluginLabel(),
    ];

    // Add known issues to #description.
    if (isset($issues[$element_type])) {
      $items = [];
      foreach ($issues[$element_type] as $issue_number => $issue_title) {
        $items[$issue_number] = "<a href=\"https://www.drupal.org/node/$issue_number\">Issue #$issue_number: $issue_title</a>";
      }
      $element['#description'] = '<b>Known Issues:</b><br/>' . implode('<br/>', $items);
    }

    // Set default element properties.
    if (isset($default_elements[$element_type])) {
      $element += $default_elements[$element_type];
    }

    // Set default options.
    if ($yamlform_element->hasProperty('options')) {
      $element['#options'] = [
        'One' => 'One',
        'Two' => 'Two',
        'Three' => 'Three',
      ];
    }

    // Set default container content.
    if ($yamlform_element->isContainer($element)) {
      if (!in_array($element_type, ['captcha', 'yamlform_wizard_page'])) {
        $element[$element_type . '_content'] = [
          '#markup' => 'This is a ' . $yamlform_element->getPluginLabel() . ' container.',
        ];
      }
    }

    $group = ($yamlform_element->isContainer($element)) ? 'containers' : 'elements';
    $left_element = $element;
    $left_element['#title'] .= ' 01';
    $right_element = $element;
    $right_element['#title'] .= ' 02';
    $data[$group]['flexbox_' . $element_type] = [
      '#type' => 'yamlform_flexbox',
      'flex_' . $element_type . '_left' => $left_element ,
      'flex_' . $element_type . '_right' => $right_element,
    ];
  }

  $yaml = file_get_contents(drupal_get_path('module', 'yamlform_test') . '/includes/yamlform_test.test_element_flexbox.yml');
  $default_elements = Yaml::decode($yaml);

  return $data['containers'] + $data['elements'] + $default_elements;
}
