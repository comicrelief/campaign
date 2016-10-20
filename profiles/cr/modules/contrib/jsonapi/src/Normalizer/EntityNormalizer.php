<?php

namespace Drupal\jsonapi\Normalizer;

use Drupal\Core\Access\AccessibleInterface;
use Drupal\Core\Cache\RefinableCacheableDependencyInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Field\EntityReferenceFieldItemList;
use Drupal\jsonapi\Configuration\ResourceConfigInterface;
use Drupal\jsonapi\Context\CurrentContextInterface;
use Drupal\jsonapi\Error\SerializableHttpException;
use Drupal\jsonapi\LinkManager\LinkManagerInterface;
use Drupal\jsonapi\Normalizer\Value\NullFieldNormalizerValue;
use Drupal\jsonapi\RelationshipInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * Converts the Drupal entity object structure to a HAL array structure.
 */
class EntityNormalizer extends NormalizerBase implements DenormalizerInterface, EntityNormalizerInterface {

  /**
   * The interface or class that this Normalizer supports.
   *
   * @var string
   */
  protected $supportedInterfaceOrClass = ContentEntityInterface::class;

  /**
   * The formats that the Normalizer can handle.
   *
   * @var array
   */
  protected $formats = array('api_json');

  /**
   * The link manager.
   *
   * @var \Drupal\jsonapi\LinkManager\LinkManagerInterface
   */
  protected $linkManager;

  /**
   * The resource manager.
   *
   * @var \Drupal\jsonapi\Configuration\ResourceManagerInterface
   */
  protected $resourceManager;

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
   *   The link manager.
   * @param \Drupal\jsonapi\Context\CurrentContextInterface $current_context
   *   The current context.
   */
  public function __construct(LinkManagerInterface $link_manager, CurrentContextInterface $current_context) {
    $this->linkManager = $link_manager;
    $this->currentContext = $current_context;
    $this->resourceManager = $current_context->getResourceManager();
  }

  /**
   * {@inheritdoc}
   */
  public function normalize($entity, $format = NULL, array $context = array()) {
    // If the fields to use were specified, only output those field values.
    $context['resource_config'] = $this->resourceManager->get(
      $entity->getEntityTypeId(),
      $entity->bundle()
    );
    $resource_type = $context['resource_config']->getTypeName();
    // Get the bundle ID of the requested resource. This is used to determine if
    // this is a bundle level resource or an entity level resource.
    $bundle_id = $context['resource_config']->getBundleId();
    if (!empty($context['sparse_fieldset'][$resource_type])) {
      $field_names = $context['sparse_fieldset'][$resource_type];
    }
    else {
      $field_names = $this->getFieldNames($entity, $bundle_id);
    }
    /* @var Value\FieldNormalizerValueInterface[] $normalizer_values */
    $normalizer_values = [];
    foreach ($this->getFields($entity, $bundle_id) as $field_name => $field) {
      if (!in_array($field_name, $field_names)) {
        continue;
      }
      $normalizer_values[$field_name] = $this->serializeField($field, $context, $format);
    }
    // Clean all the NULL values coming from denied access.
    $normalizer_values = array_filter($normalizer_values);

    $link_context = ['link_manager' => $this->linkManager];
    $output = new Value\EntityNormalizerValue($normalizer_values, $context, $entity, $link_context);
    // Add the entity level cacheability metadata.
    $output->addCacheableDependency($entity);
    $output->addCacheableDependency($output);
    // Add the field level cacheability metadata.
    array_walk($normalizer_values, function ($normalizer_value) {
      if ($normalizer_value instanceof RefinableCacheableDependencyInterface) {
        $normalizer_value->addCacheableDependency($normalizer_value);
      }
    });
    return $output;
  }

  /**
   * Checks if the passed field is a relationship field.
   *
   * @param mixed $field
   *   The field.
   *
   * @return bool
   *   TRUE if it's a JSON API relationship.
   */
  protected function isRelationship($field) {
    return $field instanceof EntityReferenceFieldItemList || $field instanceof RelationshipInterface;
  }

  /**
   * {@inheritdoc}
   */
  public function denormalize($data, $class, $format = NULL, array $context = array()) {
    if (empty($context['resource_config']) || !$context['resource_config'] instanceof ResourceConfigInterface) {
      throw new SerializableHttpException(412, 'Missing context during denormalization.');
    }
    /* @var \Drupal\jsonapi\Configuration\ResourceConfigInterface $resource_config */
    $resource_config = $context['resource_config'];
    $bundle_id = $resource_config->getBundleId();
    $bundle_key = $this->resourceManager
      ->getEntityTypeManager()
      ->getDefinition($resource_config->getEntityTypeId())
      ->getKey('bundle');
    if ($bundle_key && $bundle_id) {
      $data[$bundle_key] = $bundle_id;
    }

    return $resource_config->getStorage()->create($data);
  }

  /**
   * Gets the field names for the given entity.
   *
   * @param mixed $entity
   *   The entity.
   *
   * @return string[]
   *   The field names.
   */
  protected function getFieldNames($entity, $bundle_id) {
    /* @var \Drupal\Core\Entity\ContentEntityInterface $entity */
    return array_keys($this->getFields($entity, $bundle_id));
  }

  /**
   * Gets the field names for the given entity.
   *
   * @param mixed $entity
   *   The entity.
   * @param string $bundle_id
   *   The bundle id.
   *
   * @return array
   *   The fields.
   */
  protected function getFields($entity, $bundle_id) {
    /* @var \Drupal\Core\Entity\ContentEntityInterface $entity */
    return $entity->getFields();
  }

  /**
   * Serializes a given field.
   *
   * @param mixed $field
   *   The field to serialize.
   * @param array $context
   *   The normalization context.
   * @param string $format
   *   The serialization format.
   *
   * @return Value\FieldNormalizerValueInterface
   *   The normalized value.
   */
  protected function serializeField($field, $context, $format) {
    /* @var \Drupal\Core\Field\FieldItemListInterface|\Drupal\jsonapi\RelationshipInterface $field */
    // Continue if the current user does not have access to view this field.
    $access = $field->access('view', $context['account'], TRUE);
    if ($field instanceof AccessibleInterface && !$access) {
      return (new NullFieldNormalizerValue())->addCacheableDependency($access);
    }
    /** @var \Drupal\jsonapi\Normalizer\Value\FieldNormalizerValue $output */
    $output = $this->serializer->normalize($field, $format, $context);
    $is_relationship = $this->isRelationship($field);
    $property_type = $is_relationship ? 'relationships' : 'attributes';
    $output->setPropertyType($property_type);

    if ($output instanceof RefinableCacheableDependencyInterface) {
      // Add the cache dependency to the field level object because we want to
      // allow the field normalizers to add extra cacheability metadata.
      $output->addCacheableDependency($access);
    }

    return $output;
  }

}
