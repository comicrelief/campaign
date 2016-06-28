<?php

namespace Drupal\entity_reference_revisions\Plugin\Diff;

use Drupal\diff\FieldDiffBuilderBase;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * This plugins offers the possibility to compare ERR fields.
 *
 * @FieldDiffBuilder(
 *   id = "entity_reference_revisions_field_diff_builder",
 *   label = @Translation("Field Diff for Paragraphs"),
 *   field_types = {
 *     "entity_reference_revisions"
 *   },
 * )
 */
class EntityReferenceRevisionsFieldDiffBuilder extends FieldDiffBuilderBase {

  /**
   * {@inheritdoc}
   */
  public function build(FieldItemListInterface $field_items) {
    $result_text = array();
    $item_counter = 0;
    /** @var \Drupal\Core\Field\Plugin\Field\FieldType\EntityReferenceItem $field_item */
    foreach ($field_items as $field_key => $field_item) {
      if (!$field_item->isEmpty() && $field_item->entity) {
        $parsed_text = $this->entityParser->parseEntity($field_item->entity);
        if (is_array($parsed_text)) {
          foreach ($parsed_text as $field_id => $field) {
            foreach ($field as $id => $text) {
              $result_text[$item_counter + $id] = $text;
            }
            $item_counter = $item_counter + $id + 1;
          }
        }
      }
    }
    return $result_text;
  }
}
