<?php

namespace Drupal\yamlform\Element;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element\Checkboxes;

/**
 * Provides a form element for a entity checkboxes.
 *
 * @FormElement("yamlform_entity_checkboxes")
 */
class YamlFormEntityCheckboxes extends Checkboxes {

  use YamlFormEntityTrait;

  /**
   * {@inheritdoc}
   */
  public static function processCheckboxes(&$element, FormStateInterface $form_state, &$complete_form) {
    self::setOptions($element);
    return parent::processCheckboxes($element, $form_state, $complete_form);
  }

}
