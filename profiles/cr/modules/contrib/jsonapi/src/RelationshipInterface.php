<?php

namespace Drupal\jsonapi;

use Drupal\Core\Entity\EntityInterface;

/**
 * Interface RelationshipInterface.
 *
 * @package Drupal\jsonapi
 */
interface RelationshipInterface {

  /**
   * Gets the cardinality.
   *
   * @return mixed
   */
  public function getCardinality();

  /**
   * Gets the hostEntity.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   */
  public function getHostEntity();

  /**
   * Sets the hostEntity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $hostEntity
   */
  public function setHostEntity(EntityInterface $hostEntity);

  /**
   * Gets the field name.
   *
   * @return string
   */
  public function getPropertyName();

  /**
   * Gets the items.
   *
   * @return array
   */
  public function getItems();

}