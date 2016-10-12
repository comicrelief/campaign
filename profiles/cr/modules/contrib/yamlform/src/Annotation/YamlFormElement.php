<?php

namespace Drupal\yamlform\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a form element annotation object.
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
 * @see \Drupal\yamlform\YamlFormElementManagerInterface
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
   * URL to the element's API documentation.
   *
   * @var string
   */
  public $api;

  /**
   * The human-readable name of the form element.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $label;

  /**
   * The category in the admin UI where the form will be listed.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $category = '';

  /**
   * Flag that defines hidden element.
   *
   * @var bool
   */
  public $hidden = FALSE;

  /**
   * Flag that defines multiline element.
   *
   * @var bool
   */
  public $multiline = FALSE;

  /**
   * Flag that defines multiple (value) element.
   *
   * @var bool
   */
  public $multiple = FALSE;

  /**
   * Flag that defines composite element.
   *
   * @var bool
   */
  public $composite = FALSE;

  /**
   * Flag that defines if #states wrapper should applied be to the element.
   *
   * @var bool
   */
  public $states_wrapper = FALSE;

}
