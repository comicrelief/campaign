<?php

/**
 * @file
 * Contains \Drupal\yamlform\Plugin\Field\FieldWidget\EntityReferenceAutocompleteWidget.
 */

namespace Drupal\yamlform\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\Plugin\Field\FieldWidget\EntityReferenceAutocompleteWidget;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'yamlform_entity_reference_autocomplete' widget.
 *
 * @FieldWidget(
 *   id = "yamlform_entity_reference_autocomplete",
 *   label = @Translation("Autocomplete"),
 *   description = @Translation("An autocomplete text field."),
 *   field_types = {
 *     "yamlform"
 *   }
 * )
 */
class YamlFormEntityReferenceAutocompleteWidget extends EntityReferenceAutocompleteWidget {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    if (!isset($items[$delta]->status)) {
      $items[$delta]->status = 1;
    }

    $element = parent::formElement($items, $delta, $element, $form, $form_state);

    $element['default_data'] = [
      '#type' => 'yamlform_codemirror_yaml',
      '#title' => $this->t('Default YAML form submission data (YAML)'),
      '#description' => $this->t('Enter YAML form submission data as name and value pairs which will be used to prepopulate the selected YAML form.'),
      '#default_value' => $items[$delta]->default_data,
    ];

    $element['status'] = [
      '#type' => 'radios',
      '#title' => $this->t('Form status'),
      '#default_value' => ($items[$delta]->status == 1) ? 1 : 0,
      '#description' => $this->t('Closing a form prevents any further submissions by any users.'),
      '#options' => [
        1 => $this->t('Open'),
        0 => $this->t('Closed'),
      ],
    ];

    return $element;
  }

}
