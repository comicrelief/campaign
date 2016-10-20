<?php

namespace Drupal\jsonapi\Normalizer\Value;

/**
 * Class EntityReferenceListNormalizerValue.
 *
 * @package Drupal\jsonapi\Normalizer\Value
 */
class RelationshipNormalizerValue extends FieldNormalizerValue implements RelationshipNormalizerValueInterface {

  /**
   * The link manager.
   *
   * @param \Drupal\jsonapi\LinkManager\LinkManagerInterface
   */
  protected $linkManager;

  /**
   * The resource config for the link generation.
   *
   * @var \Drupal\jsonapi\Configuration\ResourceConfigInterface
   */
  protected $resourceConfig;

  /**
   * The field name for the link generation.
   *
   * @var string
   */
  protected $fieldName;

  /**
   * The entity ID for the host entity.
   *
   * @var string
   */
  protected $hostEntityId;

  /**
   * Instantiate a EntityReferenceNormalizerValue object.
   *
   * @param RelationshipItemNormalizerValue[] $values
   *   The normalized result.
   * @param int $cardinality
   *   The number of fields for the field list.
   * @param array $link_context
   *   All the objects and variables needed to generate the links for this
   *   relationship.
   */
  public function __construct(array $values, $cardinality, array $link_context) {
    $this->hostEntityId = $link_context['host_entity_id'];
    $this->fieldName = $link_context['field_name'];
    $this->linkManager = $link_context['link_manager'];
    $this->resourceConfig = $link_context['resource_config'];
    array_walk($values, function ($field_item_value) {
      if (!$field_item_value instanceof RelationshipItemNormalizerValueInterface) {
        throw new \RuntimeException(sprintf('Unexpected normalizer item value for this %s.', get_called_class()));
      }
    });
    parent::__construct($values, $cardinality);
  }

  /**
   * {@inheritdoc}
   */
  public function rasterizeValue() {
    if (!$value = parent::rasterizeValue()) {
      // According to the JSON API specs empty relationships are either NULL or
      // an empty array.
      return $this->cardinality == 1 ? ['data' => NULL] : ['data' => []];
    }
    // Generate the links for the relationship.
    $route_parameters = ['related' => $this->fieldName];
    return [
      'data' => $value,
      'links' => [
        'self' => $this->linkManager->getEntityLink(
          $this->hostEntityId,
          $this->resourceConfig,
          $route_parameters,
          'relationship'
        ),
        'related' => $this->linkManager->getEntityLink(
          $this->hostEntityId,
          $this->resourceConfig,
          $route_parameters,
          'related'
        ),
      ],
    ];
  }

}
