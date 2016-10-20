<?php

namespace Drupal\jsonapi\Query;

/**
 * Class ConditionOption.
 *
 * A ConditionOption represents an option which can be applied to a query.
 *
 * @package \Drupal\jsonapi\Query\ConditionOption
 */
class ConditionOption implements QueryOptionInterface {

  /**
   * A unique key.
   *
   * @var string
   */
  protected $id;

  /**
   * A unique key representing the intended parent of this option.
   *
   * @var string|NULL
   */
  protected $parentId;

  /**
   * String representation of the entity field in to be checked.
   *
   * @var string
   */
  protected $field;

  /**
   * Value of the condition for the given field.
   *
   * @var string|string[]
   */
  protected $value;

  /**
   * Conditional operator with which to compare values.
   *
   * @var string
   */
  protected $operator;

  /**
   * The langcode of the field to check.
   *
   * @var string
   */
  protected $langcode;

  /**
   * Constructs a new ConditionOption.
   *
   * @param string $id
   *   A unique string identifier for the option.
   * @param string|\Drupal\jsonapi\Query\GroupOption $field
   *   Either a field name or a GroupOption.
   * @param mixed $value
   *   Value for comparison.
   * @param string $operator
   *   Boolean operator.
   * @param string $langcode
   *   Language of entity to compare against.
   */
  public function __construct($id, $field, $value = NULL, $operator = NULL, $langcode = NULL, $parent_id = NULL) {
    $this->id = $id;
    $this->field = $field;
    $this->value = $value;
    $this->operator = $operator;
    $this->langcode = $langcode;
    $this->parentId = $parent_id;
  }

  /**
   * {@inheritdoc}
   */
  public function id() {
    return $this->id;
  }

  /**
   * {@inheritdoc}
   */
  public function apply($query) {
    return $query->condition($this->field, $this->value, $this->operator, $this->langcode);
  }

  /**
   * Returns the id of this option's parent.
   *
   * @return string|NULL
   *  Either the id of its parent or NULL.
   */
  public function parentId() {
    return $this->parentId;
  }

}
