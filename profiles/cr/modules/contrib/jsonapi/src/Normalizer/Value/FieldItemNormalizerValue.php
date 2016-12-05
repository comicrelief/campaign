<?php

namespace Drupal\jsonapi\Normalizer\Value;

/**
 * Class FieldItemNormalizerValue.
 *
 * @package Drupal\jsonapi\Normalizer\Value
 */
class FieldItemNormalizerValue implements FieldItemNormalizerValueInterface {

  /**
   * Raw values.
   *
   * @param array
   */
  protected $raw;

  /**
   * Included entity objects.
   *
   * @param EntityNormalizerValueInterface
   */
  protected $include;

  /**
   * Instantiate a FieldItemNormalizerValue object.
   *
   * @param array $values
   *   The normalized result.
   */
  public function __construct(array $values) {
    $this->raw = $values;
  }

  /**
   * {@inheritdoc}
   */
  public function rasterizeValue() {
    // If there is only one property, then output it directly.
    return count($this->raw) == 1 ? reset($this->raw) : $this->raw;
  }

  /**
   * {@inheritdoc}
   */
  public function rasterizeIncludes() {
    return $this->include->rasterizeValue();
  }

  /**
   * {@inheritdoc}
   */
  public function setInclude(ValueExtractorInterface $include) {
    $this->include = $include;
  }

  /**
   * {@inheritdoc}
   */
  public function getInclude() {
    return $this->include;
  }

}
