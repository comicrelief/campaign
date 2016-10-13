<?php

/**
 * @file
 * Generate examples of all elements.
 */

use Drupal\Component\Utility\Unicode;
use Drupal\Component\Serialization\Yaml;

/**
 * Generate examples of all elements.
 *
 * @return array
 *   An array containing examples of all elements.
 */
function yamlform_test_example_elements() {
  $issues = _yamlform_test_issues();

  // Elements to be ignored.
  $skipped_elements = [
    'hidden',
    'table',
    'yamlform_element',
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
    'basic_elements' => [],
    'advanced_elements' => [],
  ];

  /** @var \Drupal\yamlform\YamlFormElementManagerInterface $element_manager */
  $element_manager = \Drupal::service('plugin.manager.yamlform.element');

  $definitions = $element_manager->getDefinitions();
  $definitions = $element_manager->getSortedDefinitions($definitions);
  $elements = $element_manager->getInstances();
  foreach ($definitions as $definition) {
    $element_type = $definition['id'];
    $yamlform_element = $elements[$element_type];
    if (in_array($element_type, $skipped_elements) || !empty($definition['hidden'])) {
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
      $element['#description'] = '<b>' . t('Known Issues:') . '</b><br/>' . implode('<br/>', $items);
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
      if (!in_array($element_type, ['captcha'])) {
        $element[$element_type . '_content'] = [
          '#markup' => 'This is a ' . $yamlform_element->getPluginLabel() . ' container.',
        ];
      }
    }

    $category_name = (string) $yamlform_element->getPluginDefinition()['category'] ?: 'Other elements';
    $category_id = preg_replace('/[^a-zA-Z0-9]+/', '_', Unicode::strtolower($category_name));
    if (empty($data[$category_id])) {
      $data[$category_id] = [
        '#type' => 'details',
        '#title' => $category_name,
        '#open' => TRUE,
      ];
    }
    $data[$category_id][$element_type] = $element;
  }

  // Move other elements last.
  if (isset($data['other_elements'])) {
    $other_elements = $data['other_elements'];
    unset($data['other_elements']);
    $data['other_elements'] = $other_elements;
  }

  return $data;
}
