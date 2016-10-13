<?php

namespace Drupal\yamlform\Element;

/**
 * Provides a form element for an address element.
 *
 * @FormElement("yamlform_address")
 */
class YamlFormAddress extends YamlFormCompositeBase {

  /**
   * {@inheritdoc}
   */
  public static function getCompositeElements() {
    $elements = [];
    $elements['address'] = [
      '#type' => 'textfield',
      '#title' => t('Address'),
    ];
    $elements['address_2'] = [
      '#type' => 'textfield',
      '#title' => t('Address 2'),
    ];
    $elements['city'] = [
      '#type' => 'textfield',
      '#title' => t('City/Town'),
    ];
    $elements['state_province'] = [
      '#type' => 'select',
      '#title' => t('State/Province'),
      '#options' => 'state_province_names',
    ];
    $elements['postal_code'] = [
      '#type' => 'textfield',
      '#title' => t('Zip/Postal Code'),
    ];
    $elements['country'] = [
      '#type' => 'select',
      '#title' => t('Country'),
      '#options' => 'country_names',
    ];
    return $elements;
  }

}
