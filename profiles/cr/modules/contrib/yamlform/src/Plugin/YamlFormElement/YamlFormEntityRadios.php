<?php

namespace Drupal\yamlform\Plugin\YamlFormElement;

/**
 * Provides a 'yamlform_entity_radios' element.
 *
 * @YamlFormElement(
 *   id = "yamlform_entity_radios",
 *   label = @Translation("Entity radios"),
 *   category = @Translation("Entity reference elements"),
 * )
 */
class YamlFormEntityRadios extends Radios implements YamlFormEntityReferenceInterface {

  use YamlFormEntityReferenceTrait;
  use YamlFormEntityOptionsTrait;

}
