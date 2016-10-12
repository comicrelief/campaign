<?php

/**
 * @file
 * Generate test elements with #states.
 */

use Drupal\yamlform\Entity\YamlForm;
use Drupal\Component\Serialization\Yaml;

/**
 * Generate test elements with #states.
 *
 * @return array
 *   An array containing test elements with #states.
 */
function yamlform_test_test_form_states() {
  $elements = [];

  // Visible.
  $elements['visible'] = [
    '#type' => 'details',
    '#title' => 'Test State: Visible and Required',
  ];
  $elements['visible']['visible_trigger'] = [
    '#type' => 'checkbox',
    '#title' => 'Displays and require elements',
  ];
  $elements['visible'] += _yamlform_test_form_states('visible', [
    '#states' => [
      'visible' => [
        ':input[name="visible_trigger"]' => [
          'checked' => TRUE,
        ],
      ],
      'required' => [
        ':input[name="visible_trigger"]' => [
          'checked' => TRUE,
        ],
      ],
    ],
  ]);

  // Invisible.
  $elements['invisible'] = [
    '#type' => 'details',
    '#title' => 'Test State: Invisible (Hidden) and Empty',
  ];
  $elements['invisible']['invisible_trigger'] = [
    '#type' => 'checkbox',
    '#title' => 'Hide and empty elements',
  ];
  $elements['invisible'] += _yamlform_test_form_states('invisible', [
    '#states' => [
      'invisible' => [
        ':input[name="invisible_trigger"]' => [
          'checked' => TRUE,
        ],
      ],
    ],
  ]);

  // Disable.
  $elements['disabled'] = [
    '#type' => 'details',
    '#title' => 'Test State: Disabled',
  ];
  $elements['disabled']['disabled_trigger'] = [
    '#type' => 'checkbox',
    '#title' => 'Disable elements',
  ];
  $elements['disabled'] += _yamlform_test_form_states('disabled', [
    '#states' => [
      'disabled' => [
        ':input[name="disabled_trigger"]' => [
          'checked' => TRUE,
        ],
      ],
    ],
  ]);

  return $elements;
}

/**
 * Generates a render array of example elements.
 *
 * @param string $type
 *   Type of state being generated.
 * @param array $default_properties
 *   Associative array of default element properties.
 *
 * @return array
 *   A render array of example elements
 */
function _yamlform_test_form_states($type, array $default_properties = []) {
  // Track issues related to elements #states API.
  $issues = _yamlform_test_issues();

  // Elements to be ignored.
  $skipped_elements = [
    'hidden',
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

  // Create a form which will be used to generate test data.
  $yamlform = YamlForm::create();

  /** @var \Drupal\yamlform\YamlFormSubmissionGenerateInterface $generate */
  $generate = \Drupal::service('yamlform_submission.generate');

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
      $element['#description'] = '<b>' . t('Known Issues') . ':</b><br/>' . implode('<br/>', $items);
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
      // Containers can't be disabled.
      if ($type == 'disabled') {
        continue;
      }
      if (!in_array($element_type, ['captcha', 'yamlform_wizard_page'])) {
        $element[$type . '_' . $element_type . '_content'] = [
          '#markup' => 'This is a ' . $yamlform_element->getPluginLabel() . ' container.',
        ];
      }
    }
    else {
      // Set default test data as default value.
      // Don't generate a managed file.
      if (!in_array($element_type, ['managed_file'])) {
        if ($value = $generate->getTestValue($yamlform, $element_type, $element)) {
          $element['#default_value'] = $value;
        }
      }
    }

    // Set default properties.
    $element += $default_properties;

    $group = ($yamlform_element->isContainer($element)) ? 'containers' : 'elements';
    $data[$group][$type . '_' . $element_type] = $element;
  }

  return $data['containers'] + $data['elements'];
}

/******************************************************************************/
// Generate #states API issues form as Markdown for docs/issues.md.
/******************************************************************************/

/**
 * Get #states API issues as Markdown.
 *
 * @return string
 *   #states API issues as Markdown.
 */
function yamlform_test_elements_states_issues_markdown() {
  $issues = _yamlform_test_issues();

  /** @var \Drupal\yamlform\YamlFormElementManagerInterface $element_manager */
  $element_manager = \Drupal::service('plugin.manager.yamlform.element');

  $markdown = [];
  foreach ($issues as $element_type => $element_issues) {
    $element = $element_manager->getElementInstance(['#type' => $element_type]);
    $markdown[] = '#### ' . $element->getPluginLabel() . ' (' . str_replace('yamlform_', '', $element_type) . ')';
    $markdown[] = '';
    foreach ($element_issues as $issue_number => $issue_title) {
      $markdown[] = "**[Issue #$issue_number: $issue_title](https://www.drupal.org/node/$issue_number)**";
      $markdown[] = '';
    }
  }
  return implode("\n", $markdown);
}
