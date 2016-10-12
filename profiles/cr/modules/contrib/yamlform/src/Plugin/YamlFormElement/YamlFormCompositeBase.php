<?php

namespace Drupal\yamlform\Plugin\YamlFormElement;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element as RenderElement;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\yamlform\Entity\YamlFormOptions;
use Drupal\yamlform\Utility\YamlFormElementHelper;
use Drupal\yamlform\Utility\YamlFormOptionsHelper;
use Drupal\yamlform\YamlFormElementBase;
use Drupal\yamlform\YamlFormInterface;
use Drupal\yamlform\YamlFormSubmissionInterface;

/**
 * Provides a base for composite elements.
 */
abstract class YamlFormCompositeBase extends YamlFormElementBase {

  /**
   * {@inheritdoc}
   */
  public function getRelatedTypes(array $element) {
    return [];
  }

  /**
   * Get composite elements.
   *
   * @return array
   *   An array of composite elements.
   */
  abstract protected function getCompositeElements();

  /**
   * Get initialized composite element.
   *
   * @param array &$element
   *   A composite element.
   *
   * @return array
   *   The initialized composite test element.
   */
  abstract protected function getInitializedCompositeElement(array &$element);

  /**
   * {@inheritdoc}
   */
  public function prepare(array &$element, YamlFormSubmissionInterface $yamlform_submission) {
    parent::prepare($element, $yamlform_submission);

    // If #flexbox is not set or an empty string, determine if the
    // form is using a flexbox layout.
    if (!isset($element['#flexbox']) || $element['#flexbox'] === '') {
      $yamlform = $yamlform_submission->getYamlForm();
      $element['#flexbox'] = $yamlform->hasFlexboxLayout();
    }
  }

  /**
   * Format composite element value into lines of text.
   *
   * @param array $element
   *   A composite element.
   * @param array $value
   *   Composite element values.
   *
   * @return array
   *   Composite element values converted into lines of text.
   */
  protected function formatLines(array $element, array $value) {
    $items = [];
    $composite_elements = $this->getInitializedCompositeElement($element);
    foreach (RenderElement::children($composite_elements) as $composite_key) {
      if (isset($value[$composite_key]) && $value[$composite_key] != '') {
        $composite_element = $composite_elements[$composite_key];
        $composite_title = $composite_element['#title'];
        $composite_value = $value[$composite_key];
        $items[$composite_key] = "<b>$composite_title:</b> $composite_value";
      }
    }
    return $items;
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultProperties() {
    $properties = [
      'title' => '',
      'description' => '',

      'default_value' => [],
      'required' => FALSE,

      'title_display' => '',
      'description_display' => '',

      'flexbox' => '',
      'flex' => 1,
      'states' => [],
    ];

    $composite_elements = $this->getCompositeElements();
    foreach ($composite_elements as $composite_key => $composite_element) {
      // Get #type, #title, and #option from composite elements.
      foreach ($composite_element as $composite_property_key => $composite_property_value) {
        if (in_array($composite_property_key, ['#type', '#title', '#options'])) {
          $property_key = str_replace('#', $composite_key . '__', $composite_property_key);
          if ($composite_property_value instanceof TranslatableMarkup) {
            $properties[$property_key] = (string) $composite_property_value;
          }
          else {
            $properties[$property_key] = $composite_property_value;
          }
        }
      }
      if (isset($properties[$composite_key . '__type'])) {
        $properties['default_value'][$composite_key] = '';
        $properties[$composite_key . '__description'] = FALSE;
        $properties[$composite_key . '__required'] = FALSE;
        $properties[$composite_key . '__placeholder'] = '';
      }
      $properties[$composite_key . '__access'] = TRUE;
    }
    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function getTableColumn(array $element) {
    $key = $element['#yamlform_key'];
    $title = $element['#title'] ?: $key;
    $is_title_displayed = YamlFormElementHelper::isTitleDisplayed($element);

    // Get the main composite element, which can't be sorted.
    $columns = parent::getTableColumn($element);
    $columns['element__' . $key]['sort'] = FALSE;

    // Get individual composite elements.
    $composite_elements = $this->getInitializedCompositeElement($element);
    foreach (RenderElement::children($composite_elements) as $composite_key) {
      $composite_element = $composite_elements[$composite_key];
      // Make sure the composite element is visible.
      $access_key = '#' . $composite_key . '__access';
      if (isset($element[$access_key]) && $element[$access_key] === FALSE) {
        continue;
      }

      // Add reference to initialized composite element so that it can be
      // used by ::formatTableColumn().
      $columns['element__' . $key . '__' . $composite_key] = [
        'title' => ($is_title_displayed ? $title . ': ' : '') . (!empty($composite_element['#title']) ? $composite_element['#title'] : $composite_key),
        'sort' => TRUE,
        'default' => FALSE,
        'key' => $key,
        'element' => $element,
        'property_name' => $composite_key,
        'composite_key' => $composite_key,
        'composite_element' => $composite_element,
        'plugin' => $this,
      ];
    }
    return $columns;
  }

  /**
   * {@inheritdoc}
   */
  public function formatTableColumn(array $element, $value, array $options = []) {
    if (isset($options['composite_key']) && isset($options['composite_element'])) {
      $composite_key = $options['composite_key'];
      $composite_element = $options['composite_element'];
      $composite_value = $value[$composite_key];
      $composite_options = [];

      return $this->elementManager->invokeMethod('formatHtml', $composite_element, $composite_value, $composite_options);
    }
    else {
      return $this->formatHtml($element, $value);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getElementSelectorOptions(array $element) {
    $title = $this->getAdminLabel($element) . ' [' . $this->getPluginLabel() . ']';
    $name = $element['#yamlform_key'];

    $selectors = [];
    $composite_elements = $this->getInitializedCompositeElement($element);
    foreach ($composite_elements as $composite_key => $composite_element) {
      $has_access = (!isset($composite_elements['#access']) || $composite_elements['#access']);
      if ($has_access && isset($composite_element['#type'])) {
        $element_handler = $this->elementManager->getElementInstance($composite_element);
        $composite_title = (isset($composite_element['#title'])) ? $composite_element['#title'] : $composite_key;

        switch ($composite_element['#type']) {
          case 'label':
            break;

          case 'yamlform_select_other':
            $selectors[":input[name=\"{$name}[{$composite_key}][select]\"]"] = $composite_title . ' [' . $this->t('Select') . ']';
            $selectors[":input[name=\"{$name}[{$composite_key}][other]\"]"] = $composite_title . ' [' . $this->t('Textfield') . ']';
            break;

          default:
            $selectors[":input[name=\"{$name}[{$composite_key}]\"]"] = $composite_title . ' [' . $element_handler->getPluginLabel() . ']';
            break;
        }
      }
    }
    return [$title => $selectors];
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    // Update #default_value description.
    $form['general']['default_value']['#description'] = $this->t("The default value of the composite form element as YAML.");

    // Update #required label.
    $form['validation']['required']['#description'] .= '<br/>' . $this->t("Checking this option only displays the required indicator next to this element's label. Please chose which elements should be required below.");

    // Display flexbox setting.
    if ($this->hasProperty('flexbox')) {
      $form['flexbox'] = [
        '#type' => 'details',
        '#title' => $this->t('Flexbox'),
        '#open' => FALSE,
      ];
      $form['flexbox']['flexbox'] = [
        '#type' => 'select',
        '#title' => $this->t('Use Flexbox'),
        '#description' => $this->t("If 'Automatic' is selected Flexbox layout will only be used if a Flexbox element is included in the form."),
        '#options' => [
          '' => $this->t('Automatic'),
          0 => $this->t('No'),
          1 => $this->t('Yes'),
        ],
      ];
    }

    $form['composite'] = [
      '#type' => 'details',
      '#title' => $this->t('@title settings', ['@title' => $this->getPluginLabel()]),
      '#open' => FALSE,
    ];
    $form['composite']['elements'] = $this->buildCompositeElementsTable();

    return $form;
  }

  /**
   * Build the composite elements settings table.
   *
   * @return array
   *   A renderable array container the composite elements settings table.
   */
  protected function buildCompositeElementsTable() {
    $header = [
      $this->t('Key'),
      $this->t('Title/Description/Placeholder'),
      $this->t('Type/Options'),
      $this->t('Required'),
      $this->t('Visible'),
    ];

    $rows = [];
    $composite_elements = $this->getCompositeElements();
    foreach ($composite_elements as $composite_key => $composite_element) {
      $title = (isset($composite_element['#title'])) ? $composite_element['#title'] : $composite_key;
      $type = isset($composite_element['#type']) ? $composite_element['#type'] : NULL;
      $t_args = ['@title' => $title];
      $attributes = ['style' => 'width: 100%; margin-bottom: 5px'];
      $state_disabled = [
        'disabled' => [
          ':input[name="properties[' . $composite_key . '__access]"]' => [
            'checked' => FALSE,
          ],
        ],
      ];

      $row = [];

      // Key.
      $row[$composite_key . '__key'] = [
        '#markup' => $composite_key,
        '#access' => TRUE,
      ];

      // Title, placeholder, and description.
      if ($type) {
        $row['title_and_description'] = [
          'data' => [
            $composite_key . '__title' => [
              '#type' => 'textfield',
              '#title' => $this->t('@title title', $t_args),
              '#title_display' => 'invisible',
              '#placeholder' => $this->t('Enter title...'),
              '#attributes' => $attributes,
              '#states' => $state_disabled,
            ],
            $composite_key . '__placeholder' => [
              '#type' => 'textfield',
              '#title' => $this->t('@title placeholder', $t_args),
              '#title_display' => 'invisible',
              '#placeholder' => $this->t('Enter placeholder...'),
              '#attributes' => $attributes,
              '#states' => $state_disabled,
            ],
            $composite_key . '__description' => [
              '#type' => 'textarea',
              '#title' => $this->t('@title description', $t_args),
              '#title_display' => 'invisible',
              '#rows' => 2,
              '#placeholder' => $this->t('Enter description...'),
              '#attributes' => $attributes,
              '#states' => $state_disabled,
            ],
          ],
        ];
      }
      else {
        $row['title_and_description'] = ['data' => ['']];
      }

      // Type and options.
      $row['type_and_options'] = [];
      switch ($type) {
        case 'select':
          if ($composite_options = $this->getCompositeElementOptions($composite_key)) {
            $row['type_and_options']['data'][$composite_key . '__type'] = [
              '#type' => 'select',
              '#required' => TRUE,
              '#options' => [
                'select' => $this->t('Select'),
                'yamlform_select_other' => $this->t('Select other'),
                'textfield' => $this->t('Text field'),
              ],
              '#attributes' => ['style' => 'width: 100%; margin-bottom: 5px'],
              '#states' => $state_disabled,
            ];
            $row['type_and_options']['data'][$composite_key . '__options'] = [
              '#type' => 'select',
              '#options' => $composite_options,
              '#required' => TRUE,
              '#attributes' => ['style' => 'width: 100%;'],
              '#states' => $state_disabled + [
                'invisible' => [
                  ':input[name="properties[' . $composite_key . '__type]"]' => [
                    'value' => 'textfield',
                  ],
                ],
              ],
            ];
          }
          else {
            $row['type_and_options']['data'][$composite_key . '__type'] = [
              '#markup' => $this->elementManager->getElementInstance($composite_element)->getPluginLabel(),
            ];
          }
          break;

        case 'tel':
          $row['type_and_options']['data'][$composite_key . '__type'] = [
            '#type' => 'select',
            '#required' => TRUE,
            '#options' => [
              'tel' => $this->t('Telephone'),
              'textfield' => $this->t('Text field'),
            ],
            '#attributes' => ['style' => 'width: 100%; margin-bottom: 5px'],
            '#states' => $state_disabled,
          ];
          break;

        default:
          $row['type_and_options']['data'][$composite_key . '__type'] = [
            '#markup' => $this->elementManager->getElementInstance($composite_element)->getPluginLabel(),
          ];
          break;
      }

      // Required.
      if ($type) {
        $row[$composite_key . '__required'] = [
          '#type' => 'checkbox',
          '#return_value' => TRUE,
        ];
      }
      else {
        $row[$composite_key . '__required'] = ['data' => ['']];
      }

      // Access.
      $row[$composite_key . '__access'] = [
        '#type' => 'checkbox',
        '#return_value' => TRUE,
      ];

      $rows[$composite_key] = $row;
    }

    return [
      '#type' => 'table',
      '#header' => $header,
    ] + $rows;
  }

  /**
   * {@inheritdoc}
   */
  public function isMultiline(array $element) {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function formatHtml(array &$element, $value, array $options = []) {
    // Return empty value.
    if (empty($value) || empty(array_filter($value))) {
      return '';
    }

    $format = $this->getFormat($element);
    switch ($format) {
      case 'list':
        $items = [];
        $composite_elements = $this->getInitializedCompositeElement($element);
        foreach (RenderElement::children($composite_elements) as $composite_key) {
          $composite_element = $composite_elements[$composite_key];
          $composite_title = (isset($composite_element['#title'])) ? $composite_element['#title'] : $composite_key;
          $composite_value = (isset($value[$composite_key])) ? $value[$composite_key] : '';
          if ($composite_value !== '') {
            $items[$composite_key] = ['#markup' => "<b>$composite_title:</b> $composite_value"];
          }
        }
        return [
          '#theme' => 'item_list',
          '#items' => $items,
        ];

      case 'raw':
        $items = [];
        $composite_elements = $this->getInitializedCompositeElement($element);
        foreach (RenderElement::children($composite_elements) as $composite_key) {
          $composite_value = (isset($value[$composite_key])) ? $value[$composite_key] : '';
          if ($composite_value !== '') {
            $items[$composite_key] = ['#markup' => "<b>$composite_key:</b> $composite_value"];
          }
        }
        return [
          '#theme' => 'item_list',
          '#items' => $items,
        ];

      default:
        $lines = $this->formatLines($element, $value);
        foreach ($lines as $key => $line) {
          if ($key == 'email') {
            $lines[$key] = [
              '#type' => 'link',
              '#title' => $line,
              '#url' => \Drupal::pathValidator()->getUrlIfValid('mailto:' . $line),
            ];
          }
          else {
            $lines[$key] = ['#markup' => $line];
          }
          $lines[$key]['#suffix'] = '<br/>';
        }
        return $lines;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getFormats() {
    return parent::getFormats() + [
      'list' => $this->t('List'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function formatText(array &$element, $value, array $options = []) {
    // Return empty value.
    if (empty(array_filter($value))) {
      return '';
    }

    $format = $this->getFormat($element);
    switch ($format) {
      case 'list':
        $items = [];
        $composite_elements = $this->getInitializedCompositeElement($element);
        foreach (RenderElement::children($composite_elements) as $composite_key) {
          $composite_element = $composite_elements[$composite_key];
          $composite_title = $composite_element['#title'];
          $composite_value = $value[$composite_key];
          if ($composite_value !== '') {
            $items[$composite_key] = "$composite_title: $composite_value";
          }
        }
        return implode("\n", $items);

      case 'raw':
        $items = [];
        $composite_elements = $this->getInitializedCompositeElement($element);
        foreach (RenderElement::children($composite_elements) as $composite_key) {
          $composite_value = $value[$composite_key];
          if ($composite_value !== '') {
            $items[$composite_key] = "$composite_key: $composite_value";
          }
        }
        return implode("\n", $items);

      default:
        $lines = $this->formatLines($element, $value);
        return implode("\n", $lines);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getExportDefaultOptions() {
    return [
      'composite_element_item_format' => 'label',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildExportOptionsForm(array &$form, FormStateInterface $form_state, array $default_values) {
    $form['composite'] = [
      '#type' => 'details',
      '#title' => $this->t('Composite element'),
      '#open' => TRUE,
      '#weight' => -10,
    ];
    $form['composite']['composite_element_item_format'] = [
      '#type' => 'radios',
      '#title' => $this->t('Composite element item format'),
      '#options' => [
        'label' => $this->t('Option labels, the human-readable value (label)'),
        'key' => $this->t('Option values, the raw value stored in the database (key)'),
      ],
      '#default_value' => $default_values['composite_element_item_format'],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildExportHeader(array $element, array $options) {
    $composite_elements = $this->getInitializedCompositeElement($element);
    $header = [];
    foreach (RenderElement::children($composite_elements) as $composite_key) {
      $composite_element = $composite_elements[$composite_key];
      if (isset($composite_element['#access']) && $composite_element['#access'] === FALSE) {
        continue;
      }

      if ($options['header_format'] == 'label' && !empty($composite_element['#title'])) {
        $header[] = $composite_element['#title'];
      }
      else {
        $header[] = $composite_key;
      }
    }

    return $this->prefixExportHeader($header, $element, $options);
  }

  /**
   * {@inheritdoc}
   */
  public function buildExportRecord(array $element, $value, array $options) {
    $record = [];
    $composite_elements = $this->getInitializedCompositeElement($element);
    foreach (RenderElement::children($composite_elements) as $composite_key) {
      $composite_element = $composite_elements[$composite_key];
      if (isset($composite_element['#access']) && $composite_element['#access'] === FALSE) {
        continue;
      }

      if ($options['composite_element_item_format'] == 'label' && $composite_element['#type'] != 'textfield' && !empty($composite_element['#options'])) {
        $record[] = YamlFormOptionsHelper::getOptionText($value[$composite_key], $composite_element['#options']);
      }
      else {
        $record[] = (isset($value[$composite_key])) ? $value[$composite_key] : NULL;
      }
    }
    return $record;
  }

  /**
   * {@inheritdoc}
   */
  public function getTestValue(array $element, YamlFormInterface $yamlform) {
    /** @var \Drupal\yamlform\YamlFormSubmissionGenerateInterface $generate */
    $generate = \Drupal::service('yamlform_submission.generate');

    $value = [];
    $composite_elements = $this->getInitializedCompositeElement($element);
    foreach (RenderElement::children($composite_elements) as $composite_key) {
      $value[$composite_key] = $generate->getTestValue($yamlform, $composite_key, $composite_elements[$composite_key]);
    }
    return [$value];
  }

  /**
   * {@inheritdoc}
   */
  public function getConfigurationFormProperties(array &$form, FormStateInterface $form_state) {
    $properties = parent::getConfigurationFormProperties($form, $form_state);
    foreach ($properties as $key => $value) {
      // Convert composite element access and required to boolean value.
      if (strpos($key, '__access') || strpos($key, '__required')) {
        $properties[$key] = (boolean) $value;
      }
      // If the entire element is required remove required property for
      // composite elements.
      if (!empty($properties['required']) && strpos($key, '__required')) {
        unset($properties[$key]);
      }
    }
    return $properties;
  }

  /**
   * Get form option keys for composite element based on the composite element's key.
   *
   * @param string $composite_key
   *   A composite element's key.
   *
   * @return array
   *   An array form options.
   */
  protected function getCompositeElementOptions($composite_key) {
    /** @var \Drupal\yamlform\YamlFormOptionsInterface[] $yamlform_options */
    $yamlform_options = YamlFormOptions::loadMultiple();
    $options = [];
    foreach ($yamlform_options as $key => $yamlform_option) {
      if (strpos($key, $composite_key) === 0) {
        $options[$key] = $yamlform_option->label();
      }
    }
    return $options;
  }

}
