<?php

namespace Drupal\ds\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a DsField annotation object.
 *
 * @Annotation
 */
class DsField extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The human-readable name of the DS plugin.
   *
   * @ingroup plugin_translatable
   *
   * @var \Drupal\Core\Annotation\Translation
   */
  public $title;

  /**
   * The entity type this plugin should work on.
   *
   * @var string
   */
  public $entity_type;

  /**
   * An array of limits for showing this field.
   *
   * In the format: "bundle|view_mode".
   *
   * @var array
   */
  public $ui_limit;

}
