<?php

namespace Drupal\yamlform\Plugin\YamlFormElement;

use Drupal\Core\Form\FormState;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element as RenderElement;
use Drupal\Core\Url as UrlGenerator;
use Drupal\yamlform\Element\YamlFormLocation as YamlFormLocationElement;
use Drupal\yamlform\YamlFormInterface;
use Drupal\yamlform\YamlFormSubmissionInterface;

/**
 * Provides an 'location' element.
 *
 * @YamlFormElement(
 *   id = "yamlform_location",
 *   label = @Translation("Location"),
 *   category = @Translation("Composite elements"),
 *   multiline = TRUE,
 *   composite = TRUE,
 *   states_wrapper = TRUE,
 * )
 */
class YamlFormLocation extends YamlFormCompositeBase {

  /**
   * {@inheritdoc}
   */
  protected function getCompositeElements() {
    return YamlFormLocationElement::getCompositeElements();
  }

  /**
   * {@inheritdoc}
   */
  protected function getInitializedCompositeElement(array &$element) {
    $form_state = new FormState();
    $form_completed = [];
    return YamlFormLocationElement::processYamlFormComposite($element, $form_state, $form_completed);
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

      'flex' => 1,
      'states' => [],

      'geolocation' => FALSE,
      'hidden' => FALSE,
      'api_key' => '',
    ];

    $composite_elements = $this->getCompositeElements();
    foreach ($composite_elements as $composite_key => $composite_element) {
      $properties[$composite_key . '__title'] = (string) $composite_element['#title'];
      if ($composite_key != 'value') {
        $properties[$composite_key . '__access'] = FALSE;
      }
    }
    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function prepare(array &$element, YamlFormSubmissionInterface $yamlform_submission) {
    parent::prepare($element, $yamlform_submission);

    // Hide all composite elements by default.
    $composite_elements = $this->getCompositeElements();
    foreach ($composite_elements as $composite_key => $composite_element) {
      if ($composite_key != 'value' && !isset($element['#' . $composite_key . '__access'])) {
        $element['#' . $composite_key . '__access'] = FALSE;
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    // Reverted #required label.
    $form['validation']['required']['#description'] = $this->t('Check this option if the user must enter a value.');

    $form['composite']['geolocation'] = [
      '#type' => 'checkbox',
      '#title' => $this->t("Use the browser's Geolocation as the default value."),
      '#description' => $this->t('The <a href="http://www.w3schools.com/html/html5_geolocation.asp">HTML Geolocation API</a> is used to get the geographical position of a user. Since this can compromise privacy, the position is not available unless the user approves it.'),
    ];
    $form['composite']['hidden'] = [
      '#type' => 'checkbox',
      '#title' => $this->t("Hide the location element and collect the browser's Geolocation in the background."),
      '#states' => [
        'visible' => [
          ':input[name="properties[geolocation]"]' => [
            'checked' => TRUE,
          ],
        ],
      ],
    ];
    $form['composite']['api_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Google Maps API key'),
      '#description' => $this->t('Google requires users to use a valid API key. Using the <a href="https://console.developers.google.com/apis">Google API Manager</a>, you can enable the <em>Google Maps JavaScript API</em>. That will create (or reuse) a <em>Browser key</em> which you can paste here.'),
    ];
    $default_api_key = \Drupal::config('yamlform.settings')->get('elements.default_google_maps_api_key');
    if ($default_api_key) {
      $form['composite']['api_key']['#description'] .= '<br/>' . $this->t('Defaults to: %value', ['%value' => $default_api_key]);
    }
    else {
      $form['composite']['api_key']['#required'] = TRUE;
      if (\Drupal::currentUser()->hasPermission('administer yamlform')) {
        $t_args = [':href' => UrlGenerator::fromRoute('yamlform.settings')->toString()];
        $form['composite']['api_key']['#description'] .= '<br/>' . $this->t('You either enter an element specific API key here or set the <a href=":href">default site-wide API key</a>.', $t_args);
      }
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function buildCompositeElementsTable() {
    $header = [
      $this->t('Key'),
      $this->t('Title'),
      $this->t('Visible'),
    ];

    $rows = [];
    $composite_elements = $this->getCompositeElements();
    foreach ($composite_elements as $composite_key => $composite_element) {
      $title = (isset($composite_element['#title'])) ? $composite_element['#title'] : $composite_key;
      $type = isset($composite_element['#type']) ? $composite_element['#type'] : NULL;
      $t_args = ['@title' => $title];
      $attributes = ['style' => 'width: 100%; margin-bottom: 5px'];

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
            ],
          ],
        ];
      }
      else {
        $row['title_and_description'] = ['data' => ['']];
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
  public function getTestValue(array $element, YamlFormInterface $yamlform) {
    // Use test values include in settings.
    return '';
  }

}
