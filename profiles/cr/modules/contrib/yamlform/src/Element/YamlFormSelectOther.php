<?php

namespace Drupal\yamlform\Element;

/**
 * Provides a form element for a select menu with an other option.
 *
 * See #empty_option and #empty_value for an explanation of various settings for
 * a select element, including behavior if #required is TRUE or FALSE.
 *
 * @FormElement("yamlform_select_other")
 */
class YamlFormSelectOther extends YamlFormOtherBase {

  /**
   * {@inheritdoc}
   */
  protected static $type = 'select';

  /**
   * {@inheritdoc}
   */
  protected static $properties = [
    '#required',
    '#options',
    '#default_value',
    '#attributes',

    '#multiple',
    '#empty_value',
    '#empty_option',
  ];

}
