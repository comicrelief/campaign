<?php

/**
 * @file
 * Contains \Drupal\rest\Plugin\views\row\DataEntityRow.
 */

namespace Drupal\rest\Plugin\views\row;

use Drupal\views\Plugin\views\row\RowPluginBase;

/**
 * Plugin which displays entities as raw data.
 *
 * @ingroup views_row_plugins
 *
 * @ViewsRow(
 *   id = "data_entity",
 *   title = @Translation("Entity"),
 *   help = @Translation("Use entities as row data."),
 *   display_types = {"data"}
 * )
 */
class DataEntityRow extends RowPluginBase {

  /**
   * {@inheritdoc}
   */
  protected $usesOptions = FALSE;

  /**
   * {@inheritdoc}
   */
  public function render($row) {
    return $row->_entity;
  }

}
