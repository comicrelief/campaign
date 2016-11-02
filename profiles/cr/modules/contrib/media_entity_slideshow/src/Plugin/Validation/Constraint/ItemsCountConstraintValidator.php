<?php

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

    if ($value->get($constraint->sourceFieldName)->isEmpty()) {
      $this->context->addViolation($constraint->message);
    }
  }

}
