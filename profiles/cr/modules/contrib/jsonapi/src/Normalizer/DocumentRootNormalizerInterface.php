<?php


namespace Drupal\jsonapi\Normalizer;

/**
 * Class DocumentRootNormalizerInterface.
 *
 * @package Drupal\jsonapi\Normalizer
 */
interface DocumentRootNormalizerInterface {

  /**
   * Build the normalizer value.
   *
   * @return \Drupal\jsonapi\Normalizer\Value\EntityNormalizerValueInterface
   *   The normalizer value.
   */
  public function buildNormalizerValue($data, $format = NULL, array $context = array());

}
