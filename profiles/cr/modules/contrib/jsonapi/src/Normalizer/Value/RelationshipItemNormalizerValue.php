<?php

namespace Drupal\jsonapi\Normalizer\Value;
use Drupal\Core\Cache\RefinableCacheableDependencyTrait;

/**
 * Class FieldItemNormalizerValue.
 *
 * @package Drupal\jsonapi\Normalizer\Value
 */
class RelationshipItemNormalizerValue extends FieldItemNormalizerValue implements RelationshipItemNormalizerValueInterface {

  use RefinableCacheableDependencyTrait;

  /**
   * Resource path.
   *
   * @param string
   */
  protected $resource;

  /**
   * Instantiates a EntityReferenceItemNormalizerValue object.
   *
   * @param array $values
   *   The values.
   * @param string $resource
   *   The resource type of the target entity.
   */
  public function __construct(array $values, $resource) {
    parent::__construct($values);
    $this->resource = $resource;
  }

  /**
   * {@inheritdoc}
   */
  public function rasterizeValue() {
    if (!$value = parent::rasterizeValue()) {
      return $value;
    }
    return [
      'type' => $this->resource->getTypeName(),
      'id' => $value,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function setResource($resource) {
    $this->resource = $resource;
  }

}
