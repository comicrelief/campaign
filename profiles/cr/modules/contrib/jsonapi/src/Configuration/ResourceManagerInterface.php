<?php


namespace Drupal\jsonapi\Configuration;

/**
 * Class ResourceManagerInterface.
 *
 * @package Drupal\jsonapi
 */
interface ResourceManagerInterface {

  /**
   * Get all the resource configuration objects.
   *
   * @return ResourceConfigInterface[]
   *   The list of resource configs representing JSON API types.
   */
  public function all();

  /**
   * Finds a resource config.
   *
   * @param string $entity_type_id
   *   The entity type id.
   * @param string $bundle_id
   *   The id for the bundle to find.
   *
   * @return ResourceConfigInterface
   *   The resource config found. NULL if none was found.
   */
  public function get($entity_type_id, $bundle_id);

  /**
   * Get the entity type manager.
   *
   * @return \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function getEntityTypeManager();

  /**
   * Entity type has a bundle.
   *
   * @param string $entity_type_id
   *   The entity type ID.
   *
   * @return bool
   *   TRUE if the provided entity type has a bundle.
   */
  public function hasBundle($entity_type_id);

}
