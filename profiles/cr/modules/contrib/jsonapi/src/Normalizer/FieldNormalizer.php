<?php

namespace Drupal\jsonapi\Normalizer;

use Drupal\Core\Field\FieldItemListInterface;
use Symfony\Component\Serializer\Exception\UnexpectedValueException;

/**
 * Converts the Drupal field structure to HAL array structure.
 */
class FieldNormalizer extends NormalizerBase {

  /**
   * The interface or class that this Normalizer supports.
   *
   * @var string
   */
  protected $supportedInterfaceOrClass = FieldItemListInterface::class;

  /**
   * The formats that the Normalizer can handle.
   *
   * @var array
   */
  protected $formats = array('api_json');

  /**
   * {@inheritdoc}
   */
  public function normalize($field, $format = NULL, array $context = array()) {
    /* @var \Drupal\Core\Field\FieldItemListInterface $field */
    return $this->normalizeFieldItems($field, $format, $context);
  }

  /**
   * {@inheritdoc}
   */
  public function denormalize($data, $class, $format = NULL, array $context = array()) {
    throw new UnexpectedValueException('Denormalization not implemented for JSON API');
  }

  /**
   * Helper function to normalize field items.
   *
   * @param \Drupal\Core\Field\FieldItemListInterface $field
   *   The field object.
   * @param string $format
   *   The format.
   * @param array $context
   *   The context array.
   *
   * @return \Drupal\jsonapi\Normalizer\Value\FieldNormalizerValue
   *   The array of normalized field items.
   */
  protected function normalizeFieldItems(FieldItemListInterface $field, $format, $context) {
    $normalizer_items = array();
    if (!$field->isEmpty()) {
      foreach ($field as $field_item) {
        $normalizer_items[] = $this->serializer->normalize($field_item, $format, $context);
      }
    }
    $cardinality = $field->getFieldDefinition()
      ->getFieldStorageDefinition()
      ->getCardinality();
    return new Value\FieldNormalizerValue($normalizer_items, $cardinality);
  }

}
