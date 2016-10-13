<?php

namespace Drupal\yamlform\Plugin\YamlFormElement;

/**
 * Provides a 'yamlform_entity_checkboxes' element.
 *
 * @YamlFormElement(
 *   id = "yamlform_entity_checkboxes",
 *   label = @Translation("Entity checkboxes"),
 *   category = @Translation("Entity reference elements"),
 *   multiple = TRUE,
 * )
 */
class YamlFormEntityCheckboxes extends Checkboxes implements YamlFormEntityReferenceInterface {

  use YamlFormEntityReferenceTrait;
  use YamlFormEntityOptionsTrait;

}
