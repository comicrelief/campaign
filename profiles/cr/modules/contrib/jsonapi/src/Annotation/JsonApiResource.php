<?php

namespace Drupal\jsonapi\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a JSON API Resource item annotation object.
 *
 * @see \Drupal\jsonapi\Plugin\JsonApiResourceManager
 * @see plugin_api
 *
 * @Annotation
 */
class JsonApiResource extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The label of the plugin.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $label;

  /**
   * The entity type ID.
   *
   * @var string
   */
  public $entityType;

  /**
   * The bundle ID.
   *
   * @var string
   */
  public $bundle;

  /**
   * Information about the data resources.
   *
   * @var array
   */
  public $data;

  /**
   * Information about the data resources.
   *
   * @var array
   */
  public $schema;

  /**
   * TRUE if the plugin is enabled.
   *
   * @var bool
   */
  public $enabled;

}
