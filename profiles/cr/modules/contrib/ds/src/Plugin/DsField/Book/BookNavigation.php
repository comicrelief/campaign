<?php

/**
 * @file
 * Contains \Drupal\ds\Plugin\DsField\BookNavigation.
 */

namespace Drupal\ds\Plugin\DsField\Book;

use Drupal\ds\Plugin\DsField\DsFieldBase;

/**
 * Plugin that the book navigation.
 *
 * @DsField(
 *   id = "book_navigation",
 *   title = @Translation("Book navigation"),
 *   entity_type = "node",
 *   provider = "book"
 * )
 */
class BookNavigation extends DsFieldBase {

  /**
   * {@inheritdoc}
   */
  public function isAllowed() {

    // We only allow the 'full' view mode
    if ($this->viewMode() != 'full') {
      return FALSE;
    }

    // Get all the allowed types
    $types = \Drupal::config('book.settings')->get('allowed_types');

    if (!empty($types)) {
      foreach ($types as $type) {
        if ($type)
         return TRUE;
      }
    }

    // Return false when there where no displays
    return FALSE;
  }

}
