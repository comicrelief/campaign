<?php

namespace Drupal\jsonapi\Normalizer\Value;

use Drupal\Core\Cache\RefinableCacheableDependencyInterface;

/**
 * Class EntityReferenceItemNormalizerValueInterface.
 *
 * @package Drupal\jsonapi\Normalizer\Value
 */
interface RelationshipItemNormalizerValueInterface extends FieldItemNormalizerValueInterface, RefinableCacheableDependencyInterface {

  /**
   * Sets the resource.
   *
   * @param string $resource
   *   The resource to set.
   */
  public function setResource($resource);

}
