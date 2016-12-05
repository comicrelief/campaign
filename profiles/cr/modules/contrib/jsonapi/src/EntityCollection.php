<?php

namespace Drupal\jsonapi;

/**
 * Class EntityCollection.
 *
 * @package Drupal\jsonapi
 */
class EntityCollection implements EntityCollectionInterface {

  /**
   * Entity storage.
   *
   * @var array
   */
  protected $entities;

  /**
   * Holds a boolean indicating if there is a next page.
   *
   * @var bool
   */
  protected $hasNextPage;

  /**
   * Instantiates a EntityCollection object.
   *
   * @param array $entities
   *   The entities for the collection.
   */
  public function __construct(array $entities) {
    $this->entities = array_filter(array_values($entities));
  }

  /**
   * Returns an iterator for entities.
   *
   * @return \ArrayIterator
   *   An \ArrayIterator instance
   */
  public function getIterator() {
    return new \ArrayIterator($this->entities);
  }

  /**
   * Returns the number of entities.
   *
   * @return int
   *   The number of parameters
   */
  public function count() {
    return count($this->entities);
  }

  /**
   * {@inheritdoc}
   */
  public function toArray() {
    return $this->entities;
  }

  /**
   * {@inheritdoc}
   */
  public function hasNextPage() {
    return (bool) $this->hasNextPage;
  }

  /**
   * {@inheritdoc}
   */
  public function setHasNextPage($has_next_page) {
    return $this->hasNextPage = (bool) $has_next_page;
  }

}
