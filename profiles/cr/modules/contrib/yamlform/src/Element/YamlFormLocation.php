<?php

namespace Drupal\yamlform\Element;

use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a form element for a location element.
 *
 * @FormElement("yamlform_location")
 */
class YamlFormLocation extends YamlFormCompositeBase {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    return parent::getInfo() + [
      '#api_key' => '',
      '#hidden' => FALSE,
      '#geolocation' => FALSE,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function getCompositeElements() {
    // @see https://developers.google.com/maps/documentation/javascript/geocoding#GeocodingAddressTypes
    $attributes = [];
    $attributes['lat'] = [
      '#title' => t('Latitude'),
    ];
    $attributes['lng'] = [
      '#title' => t('Longitude'),
    ];
    $attributes['location'] = [
      '#title' => t('Location'),
    ];
    $attributes['formatted_address'] = [
      '#title' => t('Formatted Address'),
    ];
    $attributes['street_address'] = [
      '#title' => t('Street Address'),
    ];
    $attributes['street_number'] = [
      '#title' => t('Street Number'),
    ];
    $attributes['postal_code'] = [
      '#title' => t('Postal Code'),
    ];
    $attributes['locality'] = [
      '#title' => t('Locality'),
    ];
    $attributes['sublocality'] = [
      '#title' => t('City'),
    ];
    $attributes['administrative_area_level_1'] = [
      '#title' => t('State/Province'),
    ];
    $attributes['country'] = [
      '#title' => t('Country'),
    ];
    $attributes['country_short'] = [
      '#title' => t('Country Code'),
    ];

    foreach ($attributes as $name => &$attribute_element) {
      $attribute_element['#type'] = 'textfield';

      $attribute_element['#attributes'] = [
        'data-yamlform-location-attribute' => $name,
      ];
    }

    $elements = [];
    $elements['value'] = [
      '#type' => 'textfield',
      '#title' => t('Address'),
      '#attributes' => [
        'class' => ['yamlform-location-geocomplete'],
      ],
    ];
    $elements += $attributes;
    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public static function preRenderCompositeFormElement($element) {
    $element = YamlFormCompositeBase::preRenderCompositeFormElement($element);

    // Hide location element form display only if #geolocation is also set.
    if (!empty($element['#hidden']) && !empty($element['#geolocation'])) {
      $element['#attributes']['style'] = 'display: none';
    }

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public static function processYamlFormComposite(&$element, FormStateInterface $form_state, &$complete_form) {
    $element = parent::processYamlFormComposite($element, $form_state, $complete_form);

    // Composite elements should always be displayed and rendered so that
    // location data can be populated, so #access is really just converting the
    // readonly elements to hidden elements.
    $composite_elements = static::getCompositeElements();
    foreach ($composite_elements as $composite_key => $composite_element) {
      if ($composite_key != 'value') {
        if (isset($element[$composite_key]['#access']) && $element[$composite_key]['#access'] === FALSE) {
          unset($element[$composite_key]['#access']);
          $element[$composite_key]['#type'] = 'hidden';
        }
        elseif (!empty($element['#hidden']) && !empty($element['#geolocation'])) {
          $element[$composite_key]['#type'] = 'hidden';
        }
        else {
          $element[$composite_key]['#attributes']['class'][] = 'yamlform-readonly';
          $element[$composite_key]['#readonly'] = 'readonly';
        }
      }
    }

    // Set required.
    if (isset($element['#required'])) {
      $element['value']['#required'] = $element['#required'];
    }

    // Set Geolocation detection attribute.
    if (!empty($element['#geolocation'])) {
      $element['value']['#attributes']['data-yamlform-location-geolocation'] = 'data-yamlform-location-geolocation';
    }

    // Writing script tags (only once) directly into the page's output to ensure
    // that Google Maps APi script is loaded using the proper API key.
    static $google_api;
    if (empty($google_api)) {
      $api_key = (!empty($element['#api_key'])) ? $element['#api_key'] : \Drupal::config('yamlform.settings')->get('elements.default_google_maps_api_key');
      $element['script'] = [
        '#markup' => '<script src="https://maps.googleapis.com/maps/api/js?key=' . $api_key . '&libraries=places"></script>',
        '#allowed_tags' => ['script'],
      ];
      $google_api = TRUE;
    }

    $element['#attached']['library'][] = 'yamlform/yamlform.element.location';

    $element['#element_validate'] = [[get_called_class(), 'validateLocation']];

    return $element;
  }

  /**
   * Validates location.
   */
  public static function validateLocation(&$element, FormStateInterface $form_state, &$complete_form) {
    $value = $element['#value'];

    $has_access = (!isset($element['#access']) || $element['#access'] === TRUE);
    if ($has_access && !empty($element['#required']) && empty($value['location'])) {
      $t_args = ['@title' => !empty($element['#title']) ? $element['#title'] : t('Location')];
      $form_state->setError($element, t('The @title is not valid.', $t_args));
    }
  }

}
