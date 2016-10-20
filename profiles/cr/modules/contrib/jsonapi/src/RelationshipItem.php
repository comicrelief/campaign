<?php

namespace Drupal\jsonapi;

use Drupal\Core\Entity\EntityInterface;
use Drupal\jsonapi\Configuration\ResourceManagerInterface;

class RelationshipItem implements RelationshipItemInterface {

  /**
   * The target key name.
   *
   * @param string
   */
  protected $targetKey = 'target_id';

  /**
   * The target entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface
   */
  protected $targetEntity;

  /**
   * The target resource config.
   *
   * @param \Drupal\jsonapi\Configuration\ResourceConfigInterface
   */
  protected $targetResourceConfig;

  /**
   * The parent relationship.
   *
   * @var RelationshipInterface
   */
  protected $parent;

  /**
   * Relationship item constructor.
   *
   * @param \Drupal\jsonapi\Configuration\ResourceManagerInterface $resource_manager
   *   The resource manager.
   * @param \Drupal\Core\Entity\EntityInterface $target_entity
   *   The entity this relationship points to.
   * @param RelationshipInterface
   *   The parent of this item.
   * @param string $target_key
   *   The key name of the target relationship.
   */
  public function __construct(ResourceManagerInterface $resource_manager, EntityInterface $target_entity, RelationshipInterface $parent, $target_key = 'target_id') {
    $this->targetResourceConfig = $resource_manager->get(
      $target_entity->getEntityTypeId(),
      $target_entity->bundle()
    );
    $this->targetKey = $target_key;
    $this->targetEntity = $target_entity;
    $this->parent = $parent;
  }

  /**
   * {@inheritdoc}
   */
  public function getTargetEntity() {
    return $this->targetEntity;
  }

  /**
   * {@inheritdoc}
   */
  public function getTargetResourceConfig() {
    return $this->targetResourceConfig;
  }

  /**
   * {@inheritdoc}
   */
  public function getValue() {
    $method = $this->getTargetResourceConfig()->getIdKey() == 'uuid' ?
      'uuid' :
      'id';
    return [$this->targetKey => $this->getTargetEntity()->{$method}()];
  }

  /**
   * {@inheritdoc}
   */
  public function getParent() {
    return $this->parent;
  }

}
