<?php

namespace Drupal\yamlform;

use Drupal\Core\Entity\EntityInterface;

/**
 * Helper class form entity methods.
 */
/**
 * Provides an interface defining a form request handler.
 */
interface YamlFormRequestInterface {

  /**
   * Get the current request's source entity.
   *
   * @param string|array $ignored_types
   *   (optional) Array of ignore entity types.
   *
   * @return \Drupal\Core\Entity\EntityInterface|null
   *   The current request's source entity.
   */
  public function getCurrentSourceEntity($ignored_types = NULL);

  /**
   * Get form associated with the current request.
   *
   * @return \Drupal\yamlform\YamlFormInterface|null
   *   The current request's form.
   */
  public function getCurrentYamlForm();

  /**
   * Get the form and source entity for the current request.
   *
   * @return array
   *   An array containing the form and source entity for the current
   *   request.
   */
  public function getYamlFormEntities();

  /**
   * Get the form submission and source entity for the current request.
   *
   * @return array
   *   An array containing the form and source entity for the current
   *   request.
   */
  public function getYamlFormSubmissionEntities();

  /**
   * Get the route name for a form/submission and source entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $yamlform_entity
   *   A form or form submission.
   * @param \Drupal\Core\Entity\EntityInterface|null $source_entity
   *   A form submission's source entity.
   * @param string $route_name
   *   The route name.
   *
   * @return string
   *   A route name prefixed with 'entity.{entity_type_id}'
   *   or just 'entity'.
   */
  public function getRouteName(EntityInterface $yamlform_entity, EntityInterface $source_entity = NULL, $route_name);

  /**
   * Get the route parameters for a form/submission and source entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $yamlform_entity
   *   A form or form submission.
   * @param \Drupal\Core\Entity\EntityInterface|null $source_entity
   *   A form submission's source entity.
   *
   * @return array
   *   An array of route parameters.
   */
  public function getRouteParameters(EntityInterface $yamlform_entity, EntityInterface $source_entity = NULL);

  /**
   * Get the base route name for a form/submission and source entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $yamlform_entity
   *   A form or form submission.
   * @param \Drupal\Core\Entity\EntityInterface|null $source_entity
   *   A form submission's source entity.
   *
   * @return string
   *   If the source entity has a form attached, 'entity.{entity_type_id}'
   *   or just 'entity'.
   */
  public function getBaseRouteName(EntityInterface $yamlform_entity, EntityInterface $source_entity = NULL);

  /**
   * Check if a source entity is attached to a form.
   *
   * @param \Drupal\Core\Entity\EntityInterface $yamlform_entity
   *   A form or form submission.
   * @param \Drupal\Core\Entity\EntityInterface|null $source_entity
   *   A form submission's source entity.
   *
   * @return bool
   *   TRUE if a form is attached to a form submission source entity.
   */
  public function isValidSourceEntity(EntityInterface $yamlform_entity, EntityInterface $source_entity = NULL);

}
