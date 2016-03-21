<?php

/**
 * @file
 * Contains \Drupal\entity_reference_revisions\Plugin\Field\FieldFormatter\EntityReferenceRevisionsFormatterBase.
 */

namespace Drupal\entity_reference_revisions\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\Plugin\Field\FieldFormatter\EntityReferenceFormatterBase;

/**
 * Parent plugin for entity reference formatters.
 */
abstract class EntityReferenceRevisionsFormatterBase extends EntityReferenceFormatterBase {

  /**
   * {@inheritdoc}
   *
   * Loads the entities referenced in that field across all the entities being
   * viewed.
   */
  public function prepareView(array $entities_items) {
    // Load the existing (non-autocreate) entities. For performance, we want to
    // use a single "multiple entity load" to load all the entities for the
    // multiple "entity reference item lists" that are being displayed. We thus
    // cannot use
    // \Drupal\Core\Field\EntityReferenceFieldItemList::referencedEntities().
    $ids = array();
    foreach ($entities_items as $items) {
      foreach ($items as $item) {
        // To avoid trying to reload non-existent entities in
        // getEntitiesToView(), explicitly mark the items where $item->entity
        // contains a valid entity ready for display. All items are initialized
        // at FALSE.
        $item->_loaded = FALSE;
        if ($item->target_revision_id !== NULL) {
          $ids[] = $item->target_revision_id;
        }
      }
    }
    if ($ids) {
      $target_type = $this->getFieldSetting('target_type');

      foreach ($ids as $id) {
        $target_entities[$id] = \Drupal::entityManager()->getStorage($target_type)->loadRevision($id);
      }
    }

    // For each item, pre-populate the loaded entity in $item->entity, and set
    // the 'loaded' flag.
    foreach ($entities_items as $items) {
      foreach ($items as $item) {
        if (isset($target_entities[$item->target_revision_id])) {
          $item->entity = $target_entities[$item->target_revision_id];
          $item->_loaded = TRUE;
        }
        elseif ($item->hasNewEntity()) {
          $item->_loaded = TRUE;
        }
      }
    }
  }

}
