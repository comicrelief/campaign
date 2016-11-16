<?php

namespace Drupal\ds\Plugin\views\Entity\Render;

use Drupal\views\ResultRow;

/**
 * Renders entities in the current language.
 */
class DefaultLanguageRenderer extends RendererBase {

  /**
   * Returns the language code associated to the given row.
   *
   * @param \Drupal\views\ResultRow $row
   *   The result row.
   *
   * @return string
   *   A language code.
   */
  public function getLangcode(ResultRow $row, $relationship = NULL) {
    return $row->_entity->getUntranslated()->language()->getId();
  }

}
