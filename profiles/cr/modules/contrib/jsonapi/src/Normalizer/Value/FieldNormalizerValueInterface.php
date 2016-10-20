<?php

namespace Drupal\jsonapi\Normalizer\Value;

use Drupal\Core\Cache\RefinableCacheableDependencyInterface;

/**
 * Class FieldNormalizerValueInterface.
 *
 * @package Drupal\jsonapi\Normalizer\Value
 */
interface FieldNormalizerValueInterface extends ValueExtractorInterface, RefinableCacheableDependencyInterface {

  /**
   * Gets the includes
   *
   * @return mixed
   *   The includes.
   */
  public function getIncludes();

  /**
   * Gets the propertyType.
   *
   * @return mixed
   *   The propertyType.
   */
  public function getPropertyType();

  /**
   * Sets the propertyType.
   *
   * @param mixed $property_type
   *   The propertyType to set.
   */
  public function setPropertyType($property_type);

  /**
   * Sets the includes.
   *
   * This is used to manually set the nested includes when using the
   * relationship as a document root in a
   * /{resource}/{id}/relationships/{fieldName}.
   *
   * @param array $includes
   *   The includes.
   */
  public function setIncludes($includes);

  /**
   * Computes all the nested includes recursively.
   *
   * @return array
   *   The includes and the nested includes.
   */
  public function getAllIncludes();

}
