<?php

namespace Drupal\jsonapi\Configuration;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Class ResourceConfig.
 *
 * This object contains all the information needed to generate all the routes
 * associated with a JSON API type. In the future this is going to be
 * constructed (maybe?) from a configuration entity.
 *
 * @package Drupal\jsonapi\Configuration
 */
class ResourceConfig implements ResourceConfigInterface {

  /**
   * Holds the configuration that is global to all the JSON API types.
   *
   * @param object
   */
  protected $globalConfig;

  /**
   * Holds the entity type manager.
   *
   * @param EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The entity type ID.
   *
   * @param string
   */
  protected $entityTypeId;

  /**
   * The bundle ID.
   *
   * @param string
   */
  protected $bundleId;

  /**
   * The type name.
   *
   * @param string
   */
  protected $typeName;

  /**
   * The base resource path.
   *
   * @param string
   */
  protected $path;

  /**
   * The class to which a payload converts to.
   *
   * @param string
   */
  protected $deserializationTargetClass;

  /**
   * {@inheritdoc}
   */
  public function getGlobalConfig() {
    return $this->globalConfig;
  }

  /**
   * {@inheritdoc}
   */
  public function getEntityTypeId() {
    return $this->entityTypeId;
  }

  /**
   * {@inheritdoc}
   */
  public function setEntityTypeId($entity_type_id) {
    $this->entityTypeId = $entity_type_id;
    $this->deserializationTargetClass = $this->entityTypeManager
      ->getDefinition($entity_type_id)
      ->getClass();
  }

  /**
   * {@inheritdoc}
   */
  public function getTypeName() {
    return $this->typeName;
  }

  /**
   * {@inheritdoc}
   */
  public function setTypeName($type_name) {
    $this->typeName = $type_name;
  }

  /**
   * {@inheritdoc}
   */
  public function getPath() {
    return $this->path;
  }

  /**
   * {@inheritdoc}
   */
  public function setPath($path) {
    $this->path = $path;
  }

  /**
   * {@inheritdoc}
   */
  public function getBundleId() {
    return $this->bundleId;
  }

  /**
   * {@inheritdoc}
   */
  public function setBundleId($bundle_id) {
    $this->bundleId = $bundle_id;
  }

  /**
   * {@inheritdoc}
   */
  public function getStorage() {
    return $this->entityTypeManager->getStorage($this->entityTypeId);
  }

  /**
   * {@inheritdoc}
   */
  public function getDeserializationTargetClass() {
    return $this->deserializationTargetClass;
  }

  /**
   * {@inheritdoc}
   */
  public function getIdKey() {
    return $this->getGlobalConfig()->get('id_field');
  }

  /**
   * Instantiates a ResourceConfig object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The configuration factory.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(ConfigFactoryInterface $config_factory, EntityTypeManagerInterface $entity_type_manager) {
    $this->globalConfig = $config_factory->get('jsonapi.resource_info');
    $this->entityTypeManager = $entity_type_manager;
  }

}
