<?php

namespace Drupal\jsonapi;

use Drupal\Core\Access\AccessibleInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\jsonapi\Configuration\ResourceManagerInterface;

/**
 * Class Relationship.
 *
 * Use this class to create a relationship in your normalizer without having an entity reference field.
 *
 * @package Drupal\jsonapi
 */
class Relationship implements RelationshipInterface, AccessibleInterface {

  /**
   * Cardinality.
   *
   * @var int
   */
  protected $cardinality;

  /**
   * The entity that holds the relationship.
   *
   * @var \Drupal\Core\Entity\EntityInterface
   */
  protected $hostEntity;

  /**
   * The field name.
   *
   * @var string
   */
  protected $propertyName;

  /**
   * The resource manager.
   *
   * @var \Drupal\jsonapi\Configuration\ResourceManagerInterface
   */
  protected $resourceManager;

  /**
   * The relationship items.
   *
   * @var array
   */
  protected $items;

  /**
   * Relationship constructor.
   *
   * @param \Drupal\jsonapi\Configuration\ResourceManagerInterface $resource_manager
   *   The resource manager.
   * @param string $field_name
   *   The name of the relationship.
   * @param int $cardinality
   *   The relationship cardinality.
   * @param \Drupal\jsonapi\EntityCollectionInterface $entities
   *   A collection of entities.
   * @param \Drupal\Core\Entity\EntityInterface $host_entity
   *   The host entity.
   * @param string $target_key
   *   The property name of the relationship id.
   */
  public function __construct(ResourceManagerInterface $resource_manager, $field_name, $cardinality = FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED, EntityCollectionInterface $entities, EntityInterface $host_entity, $target_key = 'target_id') {
    $this->resourceManager = $resource_manager;
    $this->propertyName = $field_name;
    $this->cardinality = $cardinality;
    $this->hostEntity = $host_entity;
    $this->items = [];
    foreach ($entities as $entity) {
      $this->items[] = new RelationshipItem(
        $resource_manager,
        $entity,
        $this,
        $target_key
      );
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getCardinality() {
    return $this->cardinality;
  }

  /**
   * {@inheritdoc}
   */
  public function getHostEntity() {
    return $this->hostEntity;
  }

  /**
   * {@inheritdoc}
   */
  public function setHostEntity(EntityInterface $hostEntity) {
    $this->hostEntity = $hostEntity;
  }

  /**
   * {@inheritdoc}
   */
  public function access($operation, AccountInterface $account = NULL, $return_as_object = FALSE) {
    // Hard coded to TRUE. Revisit this if we need more control over this.
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function getPropertyName() {
    return $this->propertyName;
  }

  /**
   * {@inheritdoc}
   */
  public function getItems() {
    return $this->items;
  }

}
