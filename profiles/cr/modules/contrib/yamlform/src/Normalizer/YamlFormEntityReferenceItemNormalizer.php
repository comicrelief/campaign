<?php

namespace Drupal\yamlform\Normalizer;

use Drupal\hal\Normalizer\EntityReferenceItemNormalizer;
use Drupal\yamlform\Plugin\Field\FieldType\YamlFormEntityReferenceItem;

/**
 * Defines a class for normalizing YamlFormEntityReferenceItems.
 */
class YamlFormEntityReferenceItemNormalizer extends EntityReferenceItemNormalizer {

  /**
   * The interface or class that this Normalizer supports.
   *
   * @var string
   */
  protected $supportedInterfaceOrClass = YamlFormEntityReferenceItem::class;

  /**
   * {@inheritdoc}
   */
  protected function constructValue($data, $context) {
    $value = parent::constructValue($data, $context);
    if ($value) {
      $value['default_data'] = $data['default_data'];
      $value['status'] = $data['status'];
    }
    return $value;
  }

}
