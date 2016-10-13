<?php

namespace Drupal\yamlform\Plugin\YamlFormElement;

/**
 * Provides a 'yamlform_entity_select' element.
 *
 * @YamlFormElement(
 *   id = "yamlform_entity_select",
 *   label = @Translation("Entity select"),
 *   category = @Translation("Entity reference elements"),
 * )
 */
class YamlFormEntitySelect extends Select implements YamlFormEntityReferenceInterface {

  use YamlFormEntityReferenceTrait;
  use YamlFormEntityOptionsTrait;

}
