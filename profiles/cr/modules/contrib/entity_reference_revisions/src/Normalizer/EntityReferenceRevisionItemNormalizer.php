<?php

/**
 * @file
 * Contains \Drupal\entity_reference_revisions\Normalizer\EntityReferenceRevisionItemNormalizer.
 */

namespace Drupal\entity_reference_revisions\Normalizer;

use Drupal\hal\Normalizer\EntityReferenceItemNormalizer;

/**
 * Defines a class for normalizing EntityReferenceRevisionItems.
 */
class EntityReferenceRevisionItemNormalizer extends EntityReferenceItemNormalizer {

  /**
   * The interface or class that this Normalizer supports.
   *
   * @var string
   */
  protected $supportedInterfaceOrClass = 'Drupal\entity_reference_revisions\Plugin\Field\FieldType\EntityReferenceRevisionsItem';

  /**
   * Overrides \Drupal\hal\Normalizer\FieldItemNormalizer::constructValue().
   */
  protected function constructValue($data, $context) {
    $value = parent::constructValue($data, $context);
    if ($value) {
      $value['target_revision_id'] = $value['target_id'];
    }
    return $value;
  }

}
