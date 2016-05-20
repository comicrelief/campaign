<?php

/**
 * @file
 * Contains \Drupal\panelizer\Annotation\PanelizerEntity.
 */

namespace Drupal\panelizer\Annotation;

use Drupal\Component\Annotation\PluginID;

/**
 * Defines a Panelizer entity item annotation object.
 *
 * The Plugin ID should be the same as the entity type id that this is for.
 *
 * @see \Drupal\panelizer\Plugin\PanelizerEntityManager
 * @see plugin_api
 *
 * @Annotation
 */
class PanelizerEntity extends PluginID {

}
