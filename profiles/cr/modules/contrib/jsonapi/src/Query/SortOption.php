<?php

namespace Drupal\jsonapi\Query;

/**
 * Class SortOption.
 *
 * @package \Drupal\jsonapi\Query
 */
class SortOption implements QueryOptionInterface {

  /**
   * A unique key.
   *
   * @var string
   */
  protected $id;

  /**
   * The field by which to sort.
   *
   * @var string
   */
  protected $field;

  /**
   * The direction of the sort.
   *
   * @var string
   */
  protected $direction;

  /**
   * The langcode for the sort.
   *
   * @var string
   */
  protected $langcode;

  /**
   * Creates a SortOption object.
   *
   * @param string $id
   *   An identifier for the sort options.
   * @param string $field
   *   The field by which to sort.
   * @param string $field
   *   The direction for the sort.
   * @param string $langcode
   *   The language variant of the field to sort by.
   */
  public function __construct($id, $field, $direction = 'ASC', $langcode = NULL) {
    $this->id = $id;
    $this->field = $field;
    $this->direction = $direction;
    $this->langcode = $langcode;
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
    return $query->sort($this->field, $this->direction);
  }

}
