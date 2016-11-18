<?php

/**
 * @file
 * Contains \Drupal\context\Reaction\Annotation\ContextReaction.
 */

namespace Drupal\context\Reaction\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines an context reaction annotation object.
 *
 * Plugin Namespace: Plugin\ContextReaction
 *
 * @Annotation
 */
class ContextReaction extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The human-readable name of the context reaction.
   *
   * @ingroup plugin_translatable
   *
   * @var \Drupal\Core\Annotation\Translation
   */
  public $label;

  /**
   * A brief description of the context reaction.
   *
   * This will be shown when adding or configuring this context reaction.
   *
   * @ingroup plugin_translatable
   *
   * @var \Drupal\Core\Annotation\Translation (optional)
   */
  public $description = '';

}
