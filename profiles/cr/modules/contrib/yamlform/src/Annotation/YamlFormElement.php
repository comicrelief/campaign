<?php

/**
 * @file
 * Contains \Drupal\yamlform\Annotation\YamlFormElement.
 */

namespace Drupal\yamlform\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a YAML form element annotation object.
 *
 * Plugin Namespace: Plugin\YamlFormElement.
 *
 * For a working example, see
 * \Drupal\yamlform\Plugin\YamlFormElement\Email
 *
 * @see hook_yamlform_element_info_alter()
 * @see \Drupal\yamlform\YamlFormElementInterface
 * @see \Drupal\yamlform\YamlFormElementBase
 * @see \Drupal\yamlform\YamlFormElementManager
 * @see plugin_api
 *
 * @Annotation
 */
class YamlFormElement extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The human-readable name of the YAML form element.
   *
   * @ingroup plugin_translatable
   *
   * @var \Drupal\Core\Annotation\Translation
   */
  public $label;

  /**
   * Flag that defines multiline element.
   *
   * @var boolean
   */
  public $multiline = FALSE;

}
