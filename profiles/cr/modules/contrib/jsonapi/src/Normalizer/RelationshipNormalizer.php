<?php

namespace Drupal\jsonapi\Normalizer;

use Drupal\Core\Entity\EntityInterface;
use Drupal\jsonapi\Configuration\ResourceManagerInterface;
use Drupal\jsonapi\LinkManager\LinkManagerInterface;
use Drupal\jsonapi\Relationship;
use Symfony\Component\Serializer\Exception\UnexpectedValueException;

class RelationshipNormalizer extends NormalizerBase {

  /**
   * The interface or class that this Normalizer supports.
   *
   * @var string
   */
  protected $supportedInterfaceOrClass = Relationship::class;

  /**
   * The formats that the Normalizer can handle.
   *
   * @var array
   */
  protected $formats = array('api_json');

  /**
   * The manager for resource configuration.
   *
   * @var \Drupal\jsonapi\Configuration\ResourceManagerInterface
   */
  protected $resourceManager;

  /**
   * The link manager.
   *
   * @var \Drupal\jsonapi\LinkManager\LinkManagerInterface
   */
  protected $linkManager;

  /**
   * The document normalizer.
   *
   * @var \Drupal\jsonapi\Normalizer\DocumentRootNormalizerInterface
   */
  protected $documentRootNormalizer;

  /**
   * RelationshipNormalizer constructor.
   *
   * @param \Drupal\jsonapi\Configuration\ResourceManagerInterface $resource_manager
   *   The resource manager.
   * @param \Drupal\jsonapi\Normalizer\DocumentRootNormalizerInterface $document_root_normalizer
   *   The document root normalizer for the include.
   * @param \Drupal\jsonapi\LinkManager\LinkManagerInterface $link_manager
   *   The link manager.
   */
  public function __construct(ResourceManagerInterface $resource_manager, DocumentRootNormalizerInterface $document_root_normalizer, LinkManagerInterface $link_manager) {
    $this->resourceManager = $resource_manager;
    $this->documentRootNormalizer = $document_root_normalizer;
    $this->linkManager = $link_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function denormalize($data, $class, $format = NULL, array $context = array()) {
    throw new UnexpectedValueException('Denormalization not implemented for JSON API');
  }

  /**
   * Helper function to normalize field items.
   *
   * @param \Drupal\jsonapi\RelationshipInterface $relationship
   *   The field object.
   * @param string $format
   *   The format.
   * @param array $context
   *   The context array.
   *
   * @return array
   *   The array of normalized field items.
   */
  public function normalize($relationship, $format = NULL, array $context = array()) {
    /* @var \Drupal\jsonapi\RelationshipInterface $relationship */
    $normalizer_items = array();
    foreach ($relationship->getItems() as $relationship_item) {
      $normalizer_items[] = $this->serializer->normalize($relationship_item, $format, $context);
    }
    $cardinality = $relationship->getCardinality();
    $link_context = [
      'host_entity_id' => $context['resource_config']->getIdKey() == 'uuid' ? $relationship->getHostEntity()->uuid() : $relationship->getHostEntity()->id(),
      'field_name' => $relationship->getPropertyName(),
      'link_manager' => $this->linkManager,
      'resource_config' => $context['resource_config'],
      'host_uuid' => $relationship->getHostEntity()->uuid(),
    ];
    return new Value\RelationshipNormalizerValue($normalizer_items, $cardinality, $link_context);
  }

  /**
   * Builds the sub-context for the relationship include.
   *
   * @param array $context
   *   The serialization context.
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The related entity.
   * @param string $host_field_name
   *   The name of the field reference.
   *
   * @return array
   *   The modified new context.
   *
   * @see EntityReferenceItemNormalizer::buildSubContext()
   * @todo This is duplicated code from the reference item. Reuse code instead.
   */
  protected function buildSubContext($context, EntityInterface $entity, $host_field_name) {
    // Swap out the context for the context of the referenced resource.
    $context['resource_config'] = $this->resourceManager
      ->get($entity->getEntityTypeId(), $entity->bundle());
    // Since we're going one level down the only includes we need are the ones
    // that apply to this level as well.
    $include_candidates = array_filter($context['include'], function ($include) use ($host_field_name) {
      return strpos($include, $host_field_name . '.') === 0;
    });
    $context['include'] = array_map(function ($include) use ($host_field_name) {
      return str_replace($host_field_name . '.', '', $include);
    }, $include_candidates);
    return $context;
  }

}
