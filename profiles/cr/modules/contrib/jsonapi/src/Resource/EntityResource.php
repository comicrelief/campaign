<?php

namespace Drupal\jsonapi\Resource;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Access\AccessibleInterface;
use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Field\EntityReferenceFieldItemListInterface;
use Drupal\Core\Field\FieldTypePluginManagerInterface;
use Drupal\Core\Field\Plugin\Field\FieldType\EntityReferenceItem;
use Drupal\jsonapi\Configuration\ResourceConfigInterface;
use Drupal\jsonapi\EntityCollection;
use Drupal\jsonapi\EntityCollectionInterface;
use Drupal\jsonapi\Error\SerializableHttpException;
use Drupal\jsonapi\Query\QueryBuilderInterface;
use Drupal\jsonapi\Context\CurrentContextInterface;
use Drupal\jsonapi\Routing\Param\JsonApiParamBase;
use Drupal\jsonapi\Routing\Param\OffsetPage;
use Drupal\rest\ResourceResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class EntityResource.
 *
 * @package Drupal\jsonapi\Resource
 */
class EntityResource implements EntityResourceInterface {

  /**
   * The resource config.
   *
   * @var \Drupal\jsonapi\Configuration\ResourceConfigInterface
   */
  protected $resourceConfig;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The field manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $fieldManager;

  /**
   * The query builder service.
   *
   * @var \Drupal\jsonapi\Query\QueryBuilderInterface
   */
  protected $queryBuilder;

  /**
   * The current context service.
   *
   * @var \Drupal\jsonapi\Context\CurrentContextInterface
   */
  protected $currentContext;

  /**
   * The current context service.
   *
   * @var \Drupal\Core\Field\FieldTypePluginManagerInterface
   */
  protected $pluginManager;

  /**
   * Instantiates a EntityResource object.
   *
   * @param \Drupal\jsonapi\Configuration\ResourceConfigInterface $resource_config
   *   The configuration for the resource.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\jsonapi\Query\QueryBuilderInterface $query_builder
   *   The query builder.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $field_manager
   *   The entity type field manager.
   * @param \Drupal\jsonapi\Context\CurrentContextInterface $current_context
   *   The current context.
   * @param \Drupal\Core\Field\FieldTypePluginManagerInterface $plugin_manager
   *   The plugin manager for fields.
   */
  public function __construct(ResourceConfigInterface $resource_config, EntityTypeManagerInterface $entity_type_manager, QueryBuilderInterface $query_builder, EntityFieldManagerInterface $field_manager, CurrentContextInterface $current_context, FieldTypePluginManagerInterface $plugin_manager) {
    $this->resourceConfig = $resource_config;
    $this->entityTypeManager = $entity_type_manager;
    $this->queryBuilder = $query_builder;
    $this->fieldManager = $field_manager;
    $this->currentContext = $current_context;
    $this->pluginManager = $plugin_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function getIndividual(EntityInterface $entity, Request $request, $response_code = 200) {
    $entity_access = $entity->access('view', NULL, TRUE);
    if (!$entity_access->isAllowed()) {
      throw new SerializableHttpException(403, 'The current user is not allowed to GET the selected resource.');
    }
    $response = $this->buildWrappedResponse($entity, $response_code);
    return $response;
  }

  /**
   * Verifies that the whole entity does not violate any validation constraints.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity object.
   *
   * @throws \Drupal\jsonapi\Error\SerializableHttpException
   *   If validation errors are found.
   */
  protected function validate(EntityInterface $entity) {
    if (!$entity instanceof FieldableEntityInterface) {
      return;
    }

    $violations = $entity->validate();

    // Remove violations of inaccessible fields as they cannot stem from our
    // changes.
    $violations->filterByFieldAccess();

    if (count($violations) > 0) {
      $message = "Unprocessable Entity: validation failed.\n";
      foreach ($violations as $violation) {
        $message .= $violation->getPropertyPath() . ': ' . $violation->getMessage() . "\n";
      }
      // Instead of returning a generic 400 response we use the more specific
      // 422 Unprocessable Entity code from RFC 4918. That way clients can
      // distinguish between general syntax errors in bad serializations (code
      // 400) and semantic errors in well-formed requests (code 422).
      throw new SerializableHttpException(422, $message);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function createIndividual(EntityInterface $entity, Request $request) {
    $entity_access = $entity->access('create', NULL, TRUE);

    if (!$entity_access->isAllowed()) {
      throw new SerializableHttpException(403, 'The current user is not allowed to POST the selected resource.');
    }
    $this->validate($entity);
    $entity->save();
    return $this->getIndividual($entity, $request, 201);
  }

  /**
   * {@inheritdoc}
   */
  public function patchIndividual(EntityInterface $entity, EntityInterface $parsed_entity, Request $request) {
    $entity_access = $entity->access('update', NULL, TRUE);
    if (!$entity_access->isAllowed()) {
      throw new SerializableHttpException(403, 'The current user is not allowed to GET the selected resource.');
    }
    $body = Json::decode($request->getContent());
    $data = $body['data'];
    $id_key = $this->resourceConfig->getIdKey();
    if (!method_exists($entity, $id_key) || $data['id'] != $entity->{$id_key}()) {
      throw new SerializableHttpException(400, sprintf(
        'The selected entity (%s) does not match the ID in the payload (%s).',
        $entity->{$id_key}(),
        $data['id']
      ));
    }
    $data += ['attributes' => [], 'relationships' => []];
    $field_names = array_merge(array_keys($data['attributes']), array_keys($data['relationships']));
    array_reduce($field_names, function (EntityInterface $destination, $field_name) use ($parsed_entity) {
      $this->updateEntityField($parsed_entity, $destination, $field_name);
      return $destination;
    }, $entity);

    $this->validate($entity);
    $entity->save();
    return $this->getIndividual($entity, $request);
  }

  /**
   * {@inheritdoc}
   */
  public function deleteIndividual(EntityInterface $entity, Request $request) {
    $entity_access = $entity->access('delete', NULL, TRUE);
    if (!$entity_access->isAllowed()) {
      throw new SerializableHttpException(403, 'The current user is not allowed to DELETE the selected resource.');
    }
    $entity->delete();
    return new ResourceResponse(NULL, 204);
  }

  /**
   * {@inheritdoc}
   */
  public function getCollection(Request $request) {
    // Instantiate the query for the filtering.
    $entity_type_id = $this->resourceConfig->getEntityTypeId();

    // Set the current context from the request.
    $this->currentContext->fromRequest($request);

    $params = $request->attributes->get('_route_params');
    $query = $this->getCollectionQuery($entity_type_id, $params['_json_api_params']);

    $results = $query->execute();

    $storage = $this->entityTypeManager->getStorage($entity_type_id);
    // We request N+1 items to find out if there is a next page for the pager. We may need to remove that extra item
    // before loading the entities.
    $pager_size = $query->getMetaData('pager_size');
    if ($has_next_page = $pager_size < count($results)) {
      // Drop the last result.
      array_pop($results);
    }
    // Each item of the collection data contains an array with 'entity' and
    // 'access' elements.
    $collection_data = $this->loadEntitiesWithAccess($storage, $results);
    $entity_collection = new EntityCollection(array_column($collection_data, 'entity'));
    $entity_collection->setHasNextPage($has_next_page);
    $response = $this->respondWithCollection($entity_collection, $entity_type_id);

    $access_info = array_column($collection_data, 'access');
    array_walk($access_info, function ($access) use ($response) {
      $response->addCacheableDependency($access);
    });

    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function getRelated(EntityInterface $entity, $related_field, Request $request) {
    /* @var $field_list \Drupal\Core\Field\FieldItemListInterface */
    if (!($field_list = $entity->get($related_field)) || !$this->isRelationshipField($field_list)) {
      throw new SerializableHttpException(404, sprintf('The relationship %s is not present in this resource.', $related_field));
    }
    $data_definition = $field_list->getDataDefinition();
    // TODO: Also check for access in the related.
    if (!$is_multiple = $data_definition->getFieldStorageDefinition()->isMultiple()) {
      return $this->getIndividual($field_list->entity, $request);
    }
    $entities = [];
    foreach ($field_list as $field_item) {
      /* @var \Drupal\Core\Entity\EntityInterface $entity_item */
      $entity_item = $field_item->entity;
      $entities[$entity_item->id()] = $entity_item;
    }
    $entity_collection = new EntityCollection($entities);
    $entity_type_id = $field_list->getSetting('target_type');
    return $this->respondWithCollection($entity_collection, $entity_type_id);
  }

  /**
   * {@inheritdoc}
   */
  public function getRelationship(EntityInterface $entity, $related_field, Request $request, $response_code = 200) {
    if (!($field_list = $entity->get($related_field)) || !$this->isRelationshipField($field_list)) {
      throw new SerializableHttpException(404, sprintf('The relationship %s is not present in this resource.', $related_field));
    }
    $response = $this->buildWrappedResponse($field_list, $response_code);
    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function createRelationship(EntityInterface $entity, $related_field, $parsed_field_list, Request $request) {
    if ($parsed_field_list instanceof Response) {
      // This usually means that there was an error, so there is no point on
      // processing further.
      return $parsed_field_list;
    }
    /* @var \Drupal\Core\Field\EntityReferenceFieldItemListInterface $parsed_field_list */
    $this->relationshipAccess($entity, $related_field);
    // According to the specification, you are only allowed to POST to a
    // relationship if it is a to-many relationship.
    /* @var \Drupal\Core\Field\EntityReferenceFieldItemListInterface $field_list */
    $field_list = $entity->{$related_field};
    $is_multiple = $field_list->getFieldDefinition()
      ->getFieldStorageDefinition()
      ->isMultiple();
    if (!$is_multiple) {
      throw new SerializableHttpException(409, sprintf('You can only POST to to-many relationships. %s is a to-one relationship.', $related_field));
    }

    $field_access = $field_list->access('update', NULL, TRUE);
    if (!$field_access->isAllowed()) {
      throw new SerializableHttpException(403, sprintf('The current user is not allowed to PATCH the selected field (%s).', $field_list->getName()));
    }
    // Time to save the relationship.
    foreach ($parsed_field_list as $field_item) {
      $field_list->appendItem($field_item->getValue());
    }
    $this->validate($entity);
    $entity->save();
    return $this->getRelationship($entity, $related_field, $request, 201);
  }

  /**
   * {@inheritdoc}
   */
  public function patchRelationship(EntityInterface $entity, $related_field, $parsed_field_list, Request $request) {
    if ($parsed_field_list instanceof Response) {
      // This usually means that there was an error, so there is no point on
      // processing further.
      return $parsed_field_list;
    }
    /* @var \Drupal\Core\Field\EntityReferenceFieldItemListInterface $parsed_field_list */
    $this->relationshipAccess($entity, $related_field);
    // According to the specification, PATCH works a little bit different if the
    // relationship is to-one or to-many.
    /* @var \Drupal\Core\Field\EntityReferenceFieldItemListInterface $field_list */
    $field_list = $entity->{$related_field};
    $is_multiple = $field_list->getFieldDefinition()
      ->getFieldStorageDefinition()
      ->isMultiple();
    $method = $is_multiple ? 'doPatchMultipleRelationship' : 'doPatchIndividualRelationship';
    $this->{$method}($entity, $parsed_field_list);
    $this->validate($entity);
    $entity->save();
    return $this->getRelationship($entity, $related_field, $request);
  }

  /**
   * Update a to-one relationship.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The requested entity.
   * @param \Drupal\Core\Field\EntityReferenceFieldItemListInterface $parsed_field_list
   *   The entity reference field list of items to add, or a response object in
   *   case of error.
   */
  protected function doPatchIndividualRelationship(EntityInterface $entity, EntityReferenceFieldItemListInterface $parsed_field_list) {
    if ($parsed_field_list->count() > 1) {
      throw new SerializableHttpException(400, sprintf('Provide a single relationship so to-one relationship fields (%s).', $parsed_field_list->getName()));
    }
    $this->doPatchMultipleRelationship($entity, $parsed_field_list);
  }

  /**
   * Update a to-many relationship.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The requested entity.
   * @param \Drupal\Core\Field\EntityReferenceFieldItemListInterface $parsed_field_list
   *   The entity reference field list of items to add, or a response object in
   *   case of error.
   */
  protected function doPatchMultipleRelationship(EntityInterface $entity, EntityReferenceFieldItemListInterface $parsed_field_list) {
    $field_name = $parsed_field_list->getName();
    $field_access = $parsed_field_list->access('update', NULL, TRUE);
    if (!$field_access->isAllowed()) {
      throw new SerializableHttpException(403, sprintf('The current user is not allowed to PATCH the selected field (%s).', $field_name));
    }
    $entity->{$field_name} = $parsed_field_list;
  }

  /**
   * {@inheritdoc}
   */
  public function deleteRelationship(EntityInterface $entity, $related_field, $parsed_field_list, Request $request) {
    if ($parsed_field_list instanceof Response) {
      // This usually means that there was an error, so there is no point on
      // processing further.
      return $parsed_field_list;
    }
    /* @var \Drupal\Core\Field\EntityReferenceFieldItemListInterface $parsed_field_list */
    $this->relationshipAccess($entity, $related_field);

    $field_name = $parsed_field_list->getName();
    $field_access = $parsed_field_list->access('delete', NULL, TRUE);
    if (!$field_access->isAllowed()) {
      throw new SerializableHttpException(403, sprintf('The current user is not allowed to PATCH the selected field (%s).', $field_name));
    }
    /* @var \Drupal\Core\Field\EntityReferenceFieldItemListInterface $field_list */
    $field_list = $entity->{$related_field};
    // Compute the list of current values and remove the ones in the payload.
    $current_values = $field_list->getValue();
    $deleted_values = $parsed_field_list->getValue();
    $keep_values = array_udiff($current_values, $deleted_values, function ($first, $second) {
      return reset($first) - reset($second);
    });
    // Replace the existing field with one containing the relationships to keep.
    $entity->{$related_field} = $this->pluginManager
      ->createFieldItemList($entity, $related_field, $keep_values);

    // Save the entity and return the response object.
    $this->validate($entity);
    $entity->save();
    return $this->getRelationship($entity, $related_field, $request, 201);
  }

  /**
   * Gets a basic query for a collection.
   *
   * @param string $entity_type_id
   *   The entity type for the entity query.
   * @param \Drupal\jsonapi\Routing\Param\JsonApiParamInterface[] $params
   *   The parameters for the query.
   *
   * @return \Drupal\Core\Entity\Query\QueryInterface
   *   A new query.
   */
  protected function getCollectionQuery($entity_type_id, $params) {
    $entity_type = $this->entityTypeManager->getDefinition($entity_type_id);

    $query = $this->queryBuilder->newQuery($entity_type, $params);

    // Limit this query to the bundle type for this resource.
    $bundle_id = $this->resourceConfig->getBundleId();
    if ($bundle_id && ($bundle_key = $entity_type->getKey('bundle'))) {
      $query->condition(
        $bundle_key, $bundle_id
      );
    }

    return $query;
  }

  /**
   * Gets a basic query for a collection count.
   *
   * @param string $entity_type_id
   *   The entity type for the entity query.
   * @param \Drupal\jsonapi\Routing\Param\JsonApiParamInterface[] $params
   *   The parameters for the query.
   *
   * @return \Drupal\Core\Entity\Query\QueryInterface
   *   A new query.
   */
  protected function getCollectionCountQuery($entity_type_id, $params) {
    // Override the pagination parameter to get all the available results.
    $params[OffsetPage::KEY_NAME] = new JsonApiParamBase([]);
    return $this->getCollectionQuery($entity_type_id, $params);
  }

  /**
   * Builds a response with the appropriate wrapped document.
   *
   * @param mixed $data
   *   The data to wrap.
   * @param int $response_code
   *   The response code.
   * @param array $headers
   *   An array of response headers.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The response.
   */
  protected function buildWrappedResponse($data, $response_code = 200, array $headers = []) {
    return new ResourceResponse(new DocumentWrapper($data), $response_code, $headers);
  }

  /**
   * Respond with an entity collection.
   *
   * @param \Drupal\jsonapi\EntityCollectionInterface $entity_collection
   *   The collection of entites.
   * @param string $entity_type_id
   *   The entity type.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The response.
   */
  protected function respondWithCollection(EntityCollectionInterface $entity_collection, $entity_type_id) {
    $response = $this->buildWrappedResponse($entity_collection);

    // When a new change to any entity in the resource happens, we cannot ensure
    // the validity of this cached list. Add the list tag to deal with that.
    $list_tag = $this->entityTypeManager->getDefinition($entity_type_id)
      ->getListCacheTags();
    $response->getCacheableMetadata()->setCacheTags($list_tag);
    return $response;
  }

  /**
   * Check the access to update the entity and the presence of a relationship.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity.
   * @param string $related_field
   *   The name of the field to check.
   */
  protected function relationshipAccess(EntityInterface $entity, $related_field) {
    /* @var \Drupal\Core\Field\EntityReferenceFieldItemListInterface $parsed_field_list */
    $entity_access = $entity->access('update', NULL, TRUE);
    if (!$entity_access->isAllowed()) {
      throw new SerializableHttpException(403, 'The current user is not allowed to POST the selected resource.');
    }
    if (!($field_list = $entity->get($related_field)) || !$this->isRelationshipField($field_list)) {
      throw new SerializableHttpException(404, sprintf('The relationship %s is not present in this resource.', $related_field));
    }
  }

  /**
   * Takes a field from the origin entity and puts it to the destination entity.
   *
   * @param EntityInterface $origin
   *   The entity that contains the field values.
   * @param EntityInterface $destination
   *   The entity that needs to be updated.
   * @param string $field_name
   *   The name of the field to extract and update.
   */
  protected function updateEntityField(EntityInterface $origin, EntityInterface $destination, $field_name) {
    // The update is different for configuration entities and content entities.
    if ($origin instanceof ContentEntityInterface && $destination instanceof ContentEntityInterface) {
      // First scenario: both are content entities.
      if (!$field_list = $destination->get($field_name)) {
        throw new SerializableHttpException(400, sprintf('The provided field (%s) does not exist in the entity with ID %d.', $field_name, $destination->id()));
      }
      $field_access = $field_list->access('update', NULL, TRUE);
      if (!$field_access->isAllowed()) {
        throw new SerializableHttpException(403, sprintf('The current user is not allowed to PATCH the selected field (%s).', $field_list->getName()));
      }
      $destination->{$field_name} = $origin->get($field_name);
    }
    elseif ($origin instanceof ConfigEntityInterface && $destination instanceof ConfigEntityInterface) {
      // Second scenario: both are content entities.
      $destination->set($field_name, $origin->get($field_name));
    }
    else {
      throw new SerializableHttpException(400, 'The serialized entity and the destination entity are of different types.');
    }
  }

  /**
   * Checks if is a relationship field.
   *
   * @param object $entity_field
   *   Entity definition.
   * @return bool
   *   Returns TRUE, if entity field is EntityReferenceItem.
   */
  protected function isRelationshipField($entity_field) {
    /** @var \Drupal\Core\Field\FieldTypePluginManager $field_type_manager */
    $field_type_manager = \Drupal::service('plugin.manager.field.field_type');
    $class = $field_type_manager->getPluginClass($entity_field->getDataDefinition()->getType());
    return ($class == EntityReferenceItem::class || is_subclass_of($class, EntityReferenceItem::class));
  }

  /**
   * Build a collection of the entities to respond with and access objects.
   *
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   *   The entity storage to load the entities from.
   * @param int[] $ids
   *   Array of entity IDs.
   *
   * @return array
   *   An array keyed by entity ID containing the keys:
   *     - entity: the loaded entity or an access exception.
   *     - access: the access object.
   */
  protected function loadEntitiesWithAccess(EntityStorageInterface $storage, $ids) {
    $collection_data = [];
    foreach ($storage->loadMultiple($ids) as $entity) {
      /* @var \Drupal\Core\Entity\EntityInterface $entity */
      $access = $entity->access('view', NULL, TRUE);
      // Accumulate the cacheability metadata for the access.
      $collection_data[$entity->id()] = [
        'access' => $access,
        'entity' => $entity,
      ];
      if ($entity instanceof AccessibleInterface && !$access->isAllowed()) {
        // Pass an exception to the list of things to normalize.
        $collection_data[$entity->id()]['entity'] = new SerializableHttpException(403, sprintf(
          'Access checks failed for entity %s:%s.',
          $entity->getEntityTypeId(),
          $entity->id()
        ));
      }
    }

    return $collection_data;
  }

}
