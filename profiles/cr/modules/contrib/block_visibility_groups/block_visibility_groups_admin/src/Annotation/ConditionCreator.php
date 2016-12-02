<?php

namespace Drupal\block_visibility_groups_admin\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Class GroupCreator.
 *
 * @package Drupal\block_visibility_groups_admin\Annotation
 *
 * @Annotation
 */
class ConditionCreator extends Plugin {

  public $id;

  public $label;

  public $condition_plugin;

}
