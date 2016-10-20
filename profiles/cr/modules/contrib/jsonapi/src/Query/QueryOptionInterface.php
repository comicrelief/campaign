<?php

namespace Drupal\jsonapi\Query;

/**
 * Interface QueryOptionInterface.
 *
 * @package Drupal\jsonapi\Query
 */
interface QueryOptionInterface {

  /**
   * Returns a unique id for this query.
   *
   * @return string
   *   The ID for the query.
   */
  public function id();

  /**
   * Receives a QueryInterface and applies the current QueryOption to it.
   *
   * @param \Drupal\Core\Entity\Query\QueryInterface|\Drupal\Core\Entity\Query\ConditionInterface $query
   *   A query or condition group to which this option should be applied.
   *
   * @return \Drupal\Core\Entity\Query\QueryInterface|\Drupal\Core\Entity\Query\ConditionInterface
   *   A query or condition with the current option applied to it.
   */
  public function apply($query);

}
