<?php

namespace Drupal\jsonapi\Normalizer\Value;

use Drupal\Core\Cache\RefinableCacheableDependencyTrait;
use Drupal\Core\Entity\EntityInterface;

/**
 * Class EntityNormalizerValue.
 *
 * @package Drupal\jsonapi\Normalizer\Value
 */
class EntityNormalizerValue implements EntityNormalizerValueInterface {

  use RefinableCacheableDependencyTrait;

  /**
   * The values.
   *
   * @param array
   */
  protected $values;

  /**
   * The includes.
   *
   * @param array
   */
  protected $includes;

  /**
   * The resource path.
   *
   * @param array
   */
  protected $context;

  /**
   * The resource entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface
   */
  protected $entity;

  /**
   * The link manager.
   *
   * @param \Drupal\jsonapi\LinkManager\LinkManagerInterface
   */
  protected $linkManager;

  /**
   * Instantiate a EntityNormalizerValue object.
   *
   * @param FieldNormalizerValueInterface[] $values
   *   The normalized result.
   * @param array $context
   *   The context for the normalizer.
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity.
   * @param array $link_context
   *   All the objects and variables needed to generate the links for this
   *   relationship.
   */
  public function __construct(array $values, array $context, EntityInterface $entity, array $link_context) {
    $this->values = $values;
    $this->context = $context;
    $this->entity = $entity;
    $this->linkManager = $link_context['link_manager'];
    // Get an array of arrays of includes.
    $this->includes = array_map(function ($value) {
      return $value->getIncludes();
    }, $values);
    // Flatten the includes.
    $this->includes = array_reduce($this->includes, function ($carry, $includes) {
      return array_merge($carry, $includes);
    }, []);
    // Filter the empty values.
    $this->includes = array_filter($this->includes);
    array_walk($this->includes, function ($include) {
      $this->addCacheableDependency($include);
    });
  }

  /**
   * {@inheritdoc}
   */
  public function rasterizeValue() {
    $id_key = $this->context['resource_config']->getIdKey();
    // Create the array of normalized fields, starting with the URI.
    $rasterized = [
      'type' => $this->context['resource_config']->getTypeName(),
      'id' => $id_key == 'uuid' ? $this->entity->uuid() : $this->entity->id(),
      'attributes' => [],
      'relationships' => [],
    ];
    $rasterized['links'] = [
      'self' => $this->linkManager->getEntityLink(
        $rasterized['id'],
        $this->context['resource_config'],
        [],
        'individual'
      ),
    ];

    foreach ($this->getValues() as $field_name => $normalizer_value) {
      $rasterized[$normalizer_value->getPropertyType()][$field_name] = $normalizer_value->rasterizeValue();
    }
    return array_filter($rasterized);
  }

  /**
   * {@inheritdoc}
   */
  public function rasterizeIncludes() {
    // First gather all the includes in the chain.
    return array_map(function ($include) {
      return $include->rasterizeValue();
    }, $this->getIncludes());
  }

  /**
   * {@inheritdoc}
   */
  public function getValues() {
    return $this->values;
  }

  /**
   * Gets a flattened list of includes in all the chain.
   *
   * @return EntityNormalizerValueInterface
   *   The array of included relationships.
   */
  public function getIncludes() {
    $nested_includes = array_map(function ($include) {
      return $include->getIncludes();
    }, $this->includes);
    return array_reduce(array_filter($nested_includes), function ($carry, $item) {
      return array_merge($carry, $item);
    }, $this->includes);
  }

}
