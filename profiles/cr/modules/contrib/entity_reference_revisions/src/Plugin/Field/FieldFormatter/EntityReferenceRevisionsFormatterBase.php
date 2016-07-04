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
   */
  public function prepareView(array $entities_items) {
    // Entity revision loading currently has no static/persistent cache and no
    // multiload. Simulate that the entities have been loaded by setting the
    // special _loaded property to TRUE but do not actually load them, they
    // will be loaded automatically if that didn't happen yet.
    foreach ($entities_items as $items) {
      foreach ($items as $item) {
        $item->_loaded = TRUE;
      }
    }
  }

}
