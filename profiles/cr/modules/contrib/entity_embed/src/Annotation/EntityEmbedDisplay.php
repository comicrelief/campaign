<?php

/**
 * @file
 * Contains \Drupal\entity_embed\Annotation\EntityEmbedDisplay.
 */

namespace Drupal\entity_embed\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines an Entity Embed Display plugin annotation object.
 *
 * Plugin Namespace: Plugin/entity_embed/EntityEmbedDisplay.
 *
 * For a working example, see \Drupal\entity_embed\Plugin\entity_embed\EntityEmbedDisplay\FileFieldFormatter
 *
 * @see \Drupal\entity_embed\EntityEmbedDisplay\EntityEmbedDisplayBase
 * @see \Drupal\entity_embed\EntityEmbedDisplay\EntityEmbedDisplayInterface
 * @see \Drupal\entity_embed\EntityEmbedDisplay\EntityEmbedDisplayManager
 * @see plugin_api
 *
 * @ingroup entity_embed_api
 *
 * @Annotation
 */
class EntityEmbedDisplay extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The human-readable name of the Entity Embed Display plugin.
   *
   * @ingroup plugin_translatable
   *
   * @var \Drupal\Core\Annotation\Translation
   */
  public $label = '';

  /**
   * The entity types the Entity Embed Display plugin can apply to.
   *
   * To make the Entity Embed Display plugin valid for all entity types, set
   * this value to FALSE.
   *
   * @var bool|array
   */
  public $entity_types = FALSE;

}
