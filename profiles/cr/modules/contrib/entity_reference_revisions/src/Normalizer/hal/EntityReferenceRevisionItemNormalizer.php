<?php

/**
 * @file
 * Contains \Drupal\entity_reference_revisions\Normalizer\hal\EntityReferenceRevisionItemNormalizer.
 */

namespace Drupal\entity_reference_revisions\Normalizer\hal;

use Drupal\hal\Normalizer\EntityReferenceItemNormalizer;

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
      $value['target_revision_id'] = $data['target_revision_id'];
    }
    print_r($data);
    return $value;
  }

}
