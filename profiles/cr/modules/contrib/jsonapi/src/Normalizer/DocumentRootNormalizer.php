<?php

namespace Drupal\jsonapi\Normalizer;

use Drupal\Core\Field\EntityReferenceFieldItemListInterface;
use Drupal\jsonapi\Context\CurrentContextInterface;
use Drupal\jsonapi\EntityCollectionInterface;
use Drupal\jsonapi\Resource\DocumentWrapperInterface;
use Drupal\jsonapi\LinkManager\LinkManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Class DocumentRootNormalizer.
 *
 * @package Drupal\jsonapi\Normalizer
 */
class DocumentRootNormalizer extends NormalizerBase implements DenormalizerInterface, NormalizerInterface, DocumentRootNormalizerInterface {

  /**
   * The interface or class that this Normalizer supports.
   *
   * @var string
   */
  protected $supportedInterfaceOrClass = DocumentWrapperInterface::class;

  /**
   * The link manager to get the links.
   *
   * @var \Drupal\jsonapi\LinkManager\LinkManagerInterface
   */
  protected $linkManager;

  /**
   * The current JSON API request context.
   *
   * @var \Drupal\jsonapi\Context\CurrentContextInterface
   */
  protected $currentContext;

  /**
   * Constructs an ContentEntityNormalizer object.
   *
   * @param \Drupal\jsonapi\LinkManager\LinkManagerInterface $link_manager
   *   The link manager to get the links.
   * @param \Drupal\jsonapi\Context\CurrentContextInterface $current_context
   *   The current context.
   */
  public function __construct(LinkManagerInterface $link_manager, CurrentContextInterface $current_context) {
    $this->linkManager = $link_manager;
    $this->currentContext = $current_context;
  }

  /**
   * {@inheritdoc}
   */
  public function denormalize($data, $class, $format = NULL, array $context = array()) {
    $context += [
      'on_relationship' => (bool) $this->currentContext->getCurrentRoute()
        ->getDefault('_on_relationship'),
    ];
    $normalized = [];
    if (!empty($data['data']['attributes'])) {
      $normalized = $data['data']['attributes'];
    }
    if (!empty($data['data']['relationships'])) {
      // Turn all single object relationship data fields into an array of objects.
      $relationships = array_map(function ($relationship) {
        if (isset($relationship['data']['type']) && isset($relationship['data']['id'])) {
          return ['data' => [$relationship['data']]];
        }
        else {
          return $relationship;
        }
      }, $data['data']['relationships']);

      $id_key = $this->currentContext->getResourceConfig()->getIdKey();

      // Get an array of ids for every relationship.
      $relationships = array_map(function ($relationship) use ($id_key) {
        $id_list = array_column($relationship['data'], 'id');
        if ($id_key == 'id') {
          return $id_list;
        }
        list($entity_type_id,) = explode('--', $relationship['data'][0]['type']);
        $entity_storage = $this->currentContext->getResourceManager()
          ->getEntityTypeManager()
          ->getStorage($entity_type_id);
        // In order to maintain the order ($delta) of the relationships, we need
        // to load the entities and explore the $id_key value.
        $related_entities = array_values($entity_storage
          ->loadByProperties([$id_key => $id_list]));
        $map = [];
        foreach ($related_entities as $related_entity) {
          $map[$related_entity->get($id_key)->value] = $related_entity->id();
        }
        $canonical_ids = array_map(function ($input_value) use ($map) {
          return empty($map[$input_value]) ? NULL : $map[$input_value];
        }, $id_list);

        return array_filter($canonical_ids);
      }, $relationships);

      // Add the relationship ids.
      $normalized = array_merge($normalized, $relationships);
    }
    // Overwrite the serialization target class with the one in the resource
    // config.
    $class = $context['resource_config']->getDeserializationTargetClass();

    return $this->serializer
      ->denormalize($normalized, $class, $format, $context);
  }

  /**
   * {@inheritdoc}
   */
  public function normalize($object, $format = NULL, array $context = array()) {
    $context += ['resource_config' => $this->currentContext->getResourceConfig()];
    $value_extractor = $this->buildNormalizerValue($object->getData(), $format, $context);
    if (!empty($context['cacheable_metadata'])) {
      $context['cacheable_metadata']->addCacheableDependency($value_extractor);
    }
    $normalized = $value_extractor->rasterizeValue();
    $included = array_filter($value_extractor->rasterizeIncludes());
    if (!empty($included)) {
      $normalized['included'] = $included;
    }

    return $normalized;
  }

  /**
   * Build the normalizer value.
   *
   * @return \Drupal\jsonapi\Normalizer\Value\EntityNormalizerValueInterface
   *   The normalizer value.
   */
  public function buildNormalizerValue($data, $format = NULL, array $context = array()) {
    $context += $this->expandContext($context['request']);
    if ($data instanceof EntityReferenceFieldItemListInterface) {
      $output = $this->serializer->normalize($data, $format, $context);
      // The only normalizer value that computes nested includes automatically is the DocumentRootNormalizerValue
      $output->setIncludes($output->getAllIncludes());
      return $output;
    }
    else {
      $is_collection = $data instanceof EntityCollectionInterface;
      // To improve the logical workflow deal with an array at all times.
      $entities = $is_collection ? $data->toArray() : [$data];
      $context['has_next_page'] = $is_collection ? $data->hasNextPage() : FALSE;
      $serializer = $this->serializer;
      $normalizer_values = array_map(function ($entity) use ($format, $context, $serializer) {
        return $serializer->normalize($entity, $format, $context);
      }, $entities);
    }

    return new Value\DocumentRootNormalizerValue($normalizer_values, $context, $is_collection, [
      'link_manager' => $this->linkManager,
      'has_next_page' => $context['has_next_page'],
    ]);
  }

  /**
   * Expand the context information based on the current request context.
   *
   * @param Request $request
   *   The request to get the URL params from to expand the context.
   *
   * @return array
   *   The expanded context.
   */
  protected function expandContext(Request $request) {
    $context = array(
      'account' => NULL,
      'sparse_fieldset' => NULL,
      'resource_config' => NULL,
      'include' => array_filter(explode(',', $request->query->get('include'))),
    );
    if (isset($this->currentContext)) {
      $context['resource_config'] = $this->currentContext->getResourceConfig();
    }
    if ($fields_param = $request->query->get('fields')) {
      $context['sparse_fieldset'] = array_map(function ($item) {
        return explode(',', $item);
      }, $request->query->get('fields'));
    }

    return $context;
  }

}
