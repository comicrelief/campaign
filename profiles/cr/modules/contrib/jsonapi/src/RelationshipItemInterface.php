<?php

namespace Drupal\jsonapi;

interface RelationshipItemInterface {

  /**
   * Gets the target entity.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   */
  public function getTargetEntity();

  /**
   * Gets the targetResourceConfig.
   *
   * @return mixed
   */
  public function getTargetResourceConfig();

  /**
   * Gets the relationship value.
   *
   * Defaults to the entity ID.
   *
   * @return string
   */
  public function getValue();

  /**
   * Gets the relationship object that contains this relationship item.
   *
   * @return RelationshipInterface
   */
  public function getParent();

}