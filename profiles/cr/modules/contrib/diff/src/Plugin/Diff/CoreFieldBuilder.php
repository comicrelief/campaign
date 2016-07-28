<?php

/**
 * @file
 * Contains \Drupal\diff\Plugin\Diff\CoreFieldBuilder
 */

namespace Drupal\diff\Plugin\Diff;

use Drupal\diff\FieldDiffBuilderBase;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * @FieldDiffBuilder(
 *   id = "core_field_diff_builder",
 *   label = @Translation("Core Field Diff"),
 *   field_types = {"decimal", "integer", "float", "email", "telephone",
 *     "path", "date", "changed", "uri", "string", "timestamp", "created",
 *     "string_long", "language", "uuid", "map", "datetime", "boolean"
 *   },
 * )
 */
class CoreFieldBuilder extends FieldDiffBuilderBase {

  /**
   * {@inheritdoc}
   */
  public function build(FieldItemListInterface $field_items) {
    $result = array();

    // Every item from $field_items is of type FieldItemInterface.
    foreach ($field_items as $field_key => $field_item) {
      if (!$field_item->isEmpty()) {
        $values = $field_item->getValue();
        if (isset($values['value'])) {
          $result[$field_key][] = $values['value'];
        }
      }
    }

    return $result;
  }

}
