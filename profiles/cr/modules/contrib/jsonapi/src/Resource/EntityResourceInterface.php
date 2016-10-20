<?php


namespace Drupal\jsonapi\Resource;

use Drupal\Core\Entity\EntityInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class EntityResourceInterface.
 *
 * @package Drupal\jsonapi\Resource
 */
interface EntityResourceInterface {

  /**
   * Gets the individual entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The loaded entity.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   * @param int $response_code
   *   The response code. Defaults to 200.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The response.
   */
  public function getIndividual(EntityInterface $entity, Request $request, $response_code = 200);

  /**
   * Creates an individual entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The loaded entity.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The response.
   */
  public function createIndividual(EntityInterface $entity, Request $request);

  /**
   * Patches an individual entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The loaded entity.
   * @param \Drupal\Core\Entity\EntityInterface $parsed_entity
   *   The entity with the new data.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The response.
   */
  public function patchIndividual(EntityInterface $entity, EntityInterface $parsed_entity, Request $request);

  /**
   * Deletes an individual entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The loaded entity.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The response.
   */
  public function deleteIndividual(EntityInterface $entity, Request $request);

  /**
   * Gets the collection of entities.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The response.
   */
  public function getCollection(Request $request);

  /**
   * Gets the related resource.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The requested entity.
   * @param string $related_field
   *   The related field name.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The response.
   */
  public function getRelated(EntityInterface $entity, $related_field, Request $request);

  /**
   * Gets the relationship of an entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The requested entity.
   * @param string $related_field
   *   The related field name.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   * @param int $response_code
   *   The response code. Defaults to 200.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The response.
   */
  public function getRelationship(EntityInterface $entity, $related_field, Request $request, $response_code = 200);

  /**
   * Adds a relationship to a to-many relationship.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The requested entity.
   * @param string $related_field
   *   The related field name.
   * @param mixed $parsed_field_list
   *   The entity reference field list of items to add, or a response object in
   *   case of error.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The response.
   */
  public function createRelationship(EntityInterface $entity, $related_field, $parsed_field_list, Request $request);

  /**
   * Updates the relationship of an entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The requested entity.
   * @param string $related_field
   *   The related field name.
   * @param mixed $parsed_field_list
   *   The entity reference field list of items to add, or a response object in
   *   case of error.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The response.
   */
  public function patchRelationship(EntityInterface $entity, $related_field, $parsed_field_list, Request $request);

  /**
   * Deletes the relationship of an entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The requested entity.
   * @param string $related_field
   *   The related field name.
   * @param mixed $parsed_field_list
   *   The entity reference field list of items to add, or a response object in
   *   case of error.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The response.
   */
  public function deleteRelationship(EntityInterface $entity, $related_field, $parsed_field_list, Request $request);

}
