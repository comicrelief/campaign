<?php

/**
 * @file
 * Contains \Drupal\view_mode\Plugin\Field\FieldWidget\ViewModeSelectWidget.
 */

namespace Drupal\view_mode\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Field\Plugin\Field\FieldWidget\OptionsSelectWidget;
use Drupal\Core\Entity\FieldableEntityInterface;

/**
 * Plugin implementation of the 'view_mode_select_widget' widget.
 *
 * @FieldWidget(
 *   id = "view_mode_select_widget",
 *   label = @Translation("Select list"),
 *   field_types = {
 *     "list_view_mode"
 *   }
 * )
 */
class ViewModeSelectWidget extends WidgetBase {

  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {

    // @todo add None option if field is not required?
    $value = isset($items[$delta]->value) ? $items[$delta]->value : '';

    $element += array(
      '#type' => 'select',
      '#options' => $this->getOptions($items->getEntity()),
      '#default_value' => $value,
    );

    return array('value' => $element);
  }

  /**
   * Returns the array of options for the widget.
   *
   * @return array
   *   The array of options for the widget.
   */
  protected function getOptions(FieldableEntityInterface $entity) {
      // Limit the settable options for the current user account.
    $options = $this->fieldDefinition
      ->getFieldStorageDefinition()
      ->getOptionsProvider('value', $entity)
      ->getSettableOptions();

    return $options;
  }

}
