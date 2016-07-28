<?php

/**
 * @file
 * Contains \Drupal\yamlform\Plugin\Field\FieldFormatter\YamlFormEntityReferenceEntityFormatter.
 */

namespace Drupal\yamlform\Plugin\Field\FieldFormatter;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\Plugin\Field\FieldFormatter\EntityReferenceFormatterBase;

/**
 * Plugin implementation of the 'YAML form rendered entity' formatter.
 *
 * @FieldFormatter(
 *   id = "yamlform_entity_reference_entity_view",
 *   label = @Translation("YAML form"),
 *   description = @Translation("Display the referenced YAML form with default submission data."),
 *   field_types = {
 *     "yamlform"
 *   }
 * )
 */
class YamlFormEntityReferenceEntityFormatter extends EntityReferenceFormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    foreach ($this->getEntitiesToView($items, $langcode) as $delta => $entity) {
      /** @var \Drupal\yamlform\YamlFormInterface $entity */
      if ($entity->id() && $items[$delta]->status) {
        $values = ['data' => $items[$delta]->default_data];
        $elements[$delta] = $entity->getSubmissionForm($values);
      }
      else {
        $message = NULL;
        if ($entity->id()) {
          $settings = $entity->getSettings();
          $message = $settings['form_closed_message'];
        }
        if (empty($message)) {
          $message = \Drupal::config('yamlform.settings')->get('settings.default_form_closed_message');
        }
        $elements[$delta] = [
          '#markup' => $message,
          '#allowed_tags' => Xss::getAdminTagList(),
        ];
      }
    }

    return $elements;
  }

}
