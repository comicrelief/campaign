<?php

namespace Drupal\jsonapi\Normalizer\Value;

use Drupal\Core\Cache\RefinableCacheableDependencyInterface;

/**
 * Class DocumentRootNormalizerValueInterface.
 *
 * @package Drupal\jsonapi\Normalizer\Value
 */
interface DocumentRootNormalizerValueInterface extends ValueExtractorInterface, RefinableCacheableDependencyInterface {

  /**
   * Gets a flattened list of includes in all the chain.
   *
   * @return EntityNormalizerValueInterface[]
   *   The array of included relationships.
   */
  public function getIncludes();

}
