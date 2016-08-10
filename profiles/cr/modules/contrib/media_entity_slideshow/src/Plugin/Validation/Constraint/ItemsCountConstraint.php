<?php

/**
 * @file
 * Contains \Drupal\media_entity_slideshow\Plugin\Validation\Constraint\ItemsCountConstraint.
 */

namespace Drupal\media_entity_slideshow\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Check number of slideshow items.
 *
 * @Constraint(
 *   id = "ItemsCount",
 *   label = @Translation("Slideshow items count", context = "Validation"),
 * )
 */
class ItemsCountConstraint extends Constraint {

  public $source_field_name;

  /**
   * The default violation message.
   *
   * @var string
   */
  public $message = 'At least one slideshow item must exist.';
}
