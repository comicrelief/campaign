<?php

namespace Drupal\jsonapi\Normalizer\Value;

use Drupal\Core\Cache\RefinableCacheableDependencyInterface;

/**
 * Class EntityNormalizerValueInterface.
 *
 * @package Drupal\jsonapi\Normalizer\Value
 */
interface EntityNormalizerValueInterface extends ValueExtractorInterface, RefinableCacheableDependencyInterface {

  /**
   * Gets the values.
   *
   * @return mixed
   *   The values.
   */
  public function getValues();

}
