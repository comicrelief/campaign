<?php

namespace Drupal\yamlform\Element;

/**
 * Provides a form element for an name element.
 *
 * @FormElement("yamlform_name")
 */
class YamlFormName extends YamlFormCompositeBase {

  /**
   * {@inheritdoc}
   */
  public static function getCompositeElements() {
    $elements = [];
    $elements['title'] = [
      '#type' => 'yamlform_select_other',
      '#title' => t('Title'),
      '#options' => 'titles',
    ];
    $elements['first'] = [
      '#type' => 'textfield',
      '#title' => t('First'),
    ];
    $elements['middle'] = [
      '#type' => 'textfield',
      '#title' => t('Middle'),
    ];
    $elements['last'] = [
      '#type' => 'textfield',
      '#title' => t('Last'),
    ];
    $elements['suffix'] = [
      '#type' => 'textfield',
      '#title' => t('Suffix'),
    ];
    $elements['degree'] = [
      '#type' => 'textfield',
      '#title' => t('Degree'),
    ];
    return $elements;
  }

}
