<?php

/**
 * @file
 * Generate test options example.
 */

use Drupal\Component\Serialization\Yaml;
use Drupal\yamlform\Entity\YamlFormOptions;

/**
 * Generate test options example.
 *
 * @return array
 *   An array containing test elements with Flexbox wrappers.
 */
function yamlform_test_example_options() {
  $data = [
    'general' => [
      '#type' => 'details',
      '#title' => 'General options',
      '#open' => TRUE,
    ],
    'bio' => [
      '#type' => 'details',
      '#title' => 'Biographical options',
      '#open' => TRUE,
    ],
    'location' => [
      '#type' => 'details',
      '#title' => 'Location options',
      '#open' => TRUE,
    ],
    'date' => [
      '#type' => 'details',
      '#title' => 'Date options',
      '#open' => TRUE,
    ],
    'likert' => [
      '#type' => 'details',
      '#title' => 'Likert options',
      '#open' => TRUE,
    ],
  ];
  $yamlform_options = YamlFormOptions::loadMultiple();
  foreach ($yamlform_options as $id => $yamlform_option) {
    $title = $yamlform_option->label() . ' (' . $id . ')';
    if (strpos($id, 'likert') === 0) {
      $data['likert'][$id] = [
        '#type' => 'likert',
        '#title' => $title,
        '#questions' => [
          'q1' => 'Please answer question 1?',
          'q2' => 'How about now answering question 2?',
          'q3' => 'Finally, here is question 3?',
        ],
        '#answers' => $id,
      ];
    }
    else {
      if (preg_match('/(state|country)/', $id)) {
        $group = 'location';
      }
      elseif (preg_match('/(months|days)/', $id)) {
        $group = 'date';
      }
      elseif (preg_match('/(creditcard|yes_no|days|size)/', $id)) {
        $group = 'general';
      }
      else {
        $group = 'bio';
      }
      $data[$group][$id] = [
        '#type' => 'select',
        '#title' => $title,
        '#options' => $id,
      ];

    }
  }

  $yaml = file_get_contents(drupal_get_path('module', 'yamlform_test') . '/includes/yamlform_test.example_options.yml');
  $default_elements = Yaml::decode($yaml);

  return $data + $default_elements;
}
