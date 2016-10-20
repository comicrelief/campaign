<?php

namespace Drupal\jsonapi\Normalizer;

use Symfony\Component\Serializer\Exception\UnexpectedValueException;

/**
 * Class ScalarNormalizer.
 *
 * @package Drupal\jsonapi\Normalizer
 */
class ScalarNormalizer extends NormalizerBase {

  /**
   * {@inheritdoc}
   */
  protected $formats = ['api_json'];

  /**
   * {@inheritdoc}
   */
  public function supportsNormalization($data, $format = NULL) {
    return (!$data || is_scalar($data)) && in_array($format, $this->formats);
  }


  /**
   * {@inheritdoc}
   */
  public function normalize($object, $format = NULL, array $context = array()) {
    $value = new Value\FieldItemNormalizerValue(['value' => $object]);
    return new Value\FieldNormalizerValue([$value], 1);
  }

  /**
   * {@inheritdoc}
   */
  public function denormalize($data, $class, $format = NULL, array $context = array()) {
    throw new UnexpectedValueException('Denormalization not implemented for JSON API');
  }

}
