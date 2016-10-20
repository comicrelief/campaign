<?php


namespace Drupal\jsonapi;

/**
 * Class EntityCollectionInterface.
 *
 * @package Drupal\jsonapi
 */
interface EntityCollectionInterface extends \IteratorAggregate, \Countable {

  /**
   * Returns the collection as an array.
   *
   * @return \Drupal\Core\Entity\EntityInterface[]
   *   The array of entities.
   */
  public function toArray();

  /**
   * Checks if there is a next page in the collection.
   *
   * @return bool
   *   TRUE if the collection has a next page.
   */
  public function hasNextPage();

  /**
   * Sets the has next page flag.
   *
   * Once the collection query has been executed and we build the entity collection, we now if there will be a next page
   * with extra entities.
   *
   * @param bool $has_next_page
   *   TRUE if the collection has a next page.
   */
  public function setHasNextPage($has_next_page);

}
