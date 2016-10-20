<?php

namespace Drupal\jsonapi\Normalizer\Value;

use Drupal\Core\Cache\RefinableCacheableDependencyTrait;

/**
 * Class FieldNormalizerValue.
 *
 * @package Drupal\jsonapi\Normalizer\Value
 */
class FieldNormalizerValue implements FieldNormalizerValueInterface {

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
   * The field cardinality.
   *
   * @param integer
   */
  protected $cardinality;

  /**
   * The property type. Either: 'attributes' or `relationships'.
   *
   * @var string
   */
  protected $propertyType;

  /**
   * Instantiate a FieldNormalizerValue object.
   *
   * @param FieldItemNormalizerValueInterface[] $values
   *   The normalized result.
   * @param int $cardinality
   *   The cardinality of the field list.
   */
  public function __construct(array $values, $cardinality) {
    $this->values = $values;
    $this->includes = array_map(function ($value) {
      return $value->getInclude();
    }, $values);
    $this->includes = array_filter($this->includes);
    $this->cardinality = $cardinality;
  }

  /**
   * {@inheritdoc}
   */
  public function rasterizeValue() {
    if (empty($this->values)) {
      return NULL;
    }
    return $this->cardinality == 1 ?
      $this->values[0]->rasterizeValue() :
      array_map(function ($value) {
        return $value->rasterizeValue();
      }, $this->values);
  }

  /**
   * {@inheritdoc}
   */
  public function rasterizeIncludes() {
    return array_map(function ($include) {
      return $include->rasterizeValue();
    }, $this->includes);
  }

  /**
   * {@inheritdoc}
   */
  public function getIncludes() {
    return $this->includes;
  }

  /**
   * {@inheritdoc}
   */
  public function getPropertyType() {
    return $this->propertyType;
  }

  /**
   * {@inheritdoc}
   */
  public function setPropertyType($property_type) {
    $this->propertyType = $property_type;
  }

  /**
   * {@inheritdoc}
   */
  public function setIncludes($includes) {
    $this->includes = $includes;
  }

  /**
   * {@inheritdoc}
   */
  public function getAllIncludes() {
    $nested_includes = array_map(function ($include) {
      return $include->getIncludes();
    }, $this->getIncludes());
    $includes = array_reduce(array_filter($nested_includes), function ($carry, $item) {
      return array_merge($carry, $item);
    }, $this->getIncludes());
    // Make sure we don't output duplicate includes.
    return array_values(array_reduce($includes, function ($unique_includes, $include) {
      $rasterized_include = $include->rasterizeValue();
      $unique_includes[$rasterized_include['data']['type'] . ':' . $rasterized_include['data']['id']] = $include;
      return $unique_includes;
    }, []));
  }

}
