<?php

namespace Drupal\yamlform\Element;

/**
 * Provides a form element for checkboxes with an other option.
 *
 * @FormElement("yamlform_checkboxes_other")
 */
class YamlFormCheckboxesOther extends YamlFormOtherBase {

  /**
   * {@inheritdoc}
   */
  protected static $type = 'checkboxes';

}
