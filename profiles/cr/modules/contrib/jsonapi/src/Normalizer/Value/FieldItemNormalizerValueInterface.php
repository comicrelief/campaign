<?php

namespace Drupal\jsonapi\Normalizer\Value;

/**
 * Class FieldItemNormalizerValueInterface.
 *
 * @package Drupal\jsonapi\Normalizer\Value
 */
interface FieldItemNormalizerValueInterface extends ValueExtractorInterface {

  /**
   * Add an include.
   *
   * @param ValueExtractorInterface $include
   *   The included entity.
   */
  public function setInclude(ValueExtractorInterface $include);

  /**
   * Gets the include.
   *
   * @return EntityNormalizerValueInterface
   *   The include.
   */
  public function getInclude();

}
