<?php

/**
 * @file
 * Contains \Drupal\media_entity_slideshow\Plugin\Validation\Constraint\ItemsCountConstraintValidator.
 */

namespace Drupal\media_entity_slideshow\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the ItemsCount constraint.
 */
class ItemsCountConstraintValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($value, Constraint $constraint) {
    if (!isset($value)) {
      return;
    }

    if ($value->get($constraint->source_field_name)->isEmpty()) {
      $this->context->addViolation($constraint->message);
    }
  }

}
