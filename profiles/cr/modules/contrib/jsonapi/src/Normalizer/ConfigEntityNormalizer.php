<?php

namespace Drupal\jsonapi\Normalizer;

use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\jsonapi\RelationshipInterface;

/**
 * Class ConfigEntityNormalizer.
 *
 * Converts a configuration entity into the JSON API value rasterizable object.
 *
 * @package Drupal\jsonapi\Normalizer
 */
class ConfigEntityNormalizer extends EntityNormalizer {

  /**
   * The interface or class that this Normalizer supports.
   *
   * @var string
   */
  protected $supportedInterfaceOrClass = ConfigEntityInterface::class;

  /**
   * {@inheritdoc}
   */
  protected function getFields($entity, $bundle_id) {
    /* @var \Drupal\Core\Config\Entity\ConfigEntityInterface $entity */
    return $entity->toArray();
  }

  /**
   * {@inheritdoc}
   */
  protected function serializeField($field, $context, $format) {
    $output = $this->serializer->normalize($field, $format, $context);
    if (is_array($output)) {
      // If the property is multivalue combine all of them in a single
      // Value\FieldNormalizerValue
      $data = [];
      foreach ($output as $key => $value) {
        $data[$key] = $value->rasterizeValue();
      }
      $output = new Value\FieldNormalizerValue(
        [new Value\FieldItemNormalizerValue($data)],
        1
      );
      $output->setPropertyType('attributes');
      return $output;
    }
    $field instanceof RelationshipInterface ?
      $output->setPropertyType('relationships') :
      $output->setPropertyType('attributes');
    return $output;
  }

}
