<?php


namespace Drupal\jsonapi\Configuration;

/**
 * Class ResourceConfigInterface.
 *
 * @package Drupal\jsonapi\Configuration
 */
interface ResourceConfigInterface {

  /**
   * Gets the entity type id.
   *
   * @return string
   *   The entity type id.
   */
  public function getEntityTypeId();

  /**
   * Sets the entity type id.
   *
   * @param string $entity_type_id
   *   The entityTypeId to set.
   */
  public function setEntityTypeId($entity_type_id);

  /**
   * Gets the type name.
   *
   * @return string
   *   The type name.
   */
  public function getTypeName();

  /**
   * Sets the type name.
   *
   * @param string $type_name
   *   The type name to set.
   */
  public function setTypeName($type_name);

  /**
   * Gets the path.
   *
   * @return string
   *   The path.
   */
  public function getPath();

  /**
   * Sets the path.
   *
   * @param string $path
   *   The path to set.
   */
  public function setPath($path);

  /**
   * Gets the bundle ID.
   *
   * @return string
   *   The bundleId.
   */
  public function getBundleId();

  /**
   * Sets the bundle ID.
   *
   * @param string $bundle_id
   *   The bundleId to set.
   */
  public function setBundleId($bundle_id);

  /**
   * Gets the global configuration.
   *
   * @return \Drupal\Core\Config\Config
   *   The global configuration.
   */
  public function getGlobalConfig();

  /**
   * Gets the underlying entity storage for the resource.
   *
   * @return \Drupal\Core\Entity\EntityStorageInterface
   *   The appropriate entity storage interface.
   */
  public function getStorage();

  /**
   * Gets the deserialization target class.
   *
   * @return string
   *   The deserialization target class.
   */
  public function getDeserializationTargetClass();

  /**
   * Get the entity key used for the ID.
   *
   * @return string
   *   The key.
   */
  public function getIdKey();

}
