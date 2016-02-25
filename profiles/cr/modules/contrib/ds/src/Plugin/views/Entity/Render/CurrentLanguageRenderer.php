<?php

/**
 * @file
 * Contains \Drupal\ds\Plugin\views\Entity\Render\CurrentLanguageRenderer.
 */

namespace Drupal\ds\Plugin\views\Entity\Render;

use Drupal\views\ResultRow;

/**
 * Renders entities in the current language.
 */
class CurrentLanguageRenderer extends RendererBase {

  /**
   * Returns NULL so that the current language is used.
   *
   * @param \Drupal\views\ResultRow $row
   *   The result row.
   */
  public function getLangcode(ResultRow $row) {
  }

}
