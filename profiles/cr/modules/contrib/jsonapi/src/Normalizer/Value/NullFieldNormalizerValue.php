<?php

namespace Drupal\jsonapi\Normalizer\Value;

use Drupal\Core\Cache\RefinableCacheableDependencyTrait;

class NullFieldNormalizerValue implements FieldNormalizerValueInterface {

  use RefinableCacheableDependencyTrait;

  protected $propertyType;

  public function getIncludes() {
    return [];
  }

  public function getPropertyType() {
    return $this->propertyType;
  }

  public function setPropertyType($property_type) {
    $this->propertyType = $property_type;
    return $this;
  }

  public function rasterizeValue() {
    return NULL;
  }

  public function rasterizeIncludes() {
    return [];
  }

  public function setIncludes($includes) {
    // Do nothing.
  }

  public function getAllIncludes() {
    return NULL;
  }

}
