<?php

/**
 * @file
 * Contains \Drupal\focal_point\Plugin\Field\FieldWidget\FocalPointImageWidget.
 */

namespace Drupal\focal_point\Plugin\Field\FieldWidget;

use Drupal\focal_point\FocalPoint;
use Drupal\Core\Form\FormStateInterface;
use Drupal\image\Plugin\Field\FieldWidget\ImageWidget;

/**
 * Plugin implementation of the 'image_fp' widget.
 *
 * The annotation has been intentionally omitted. Rather than create an entirely
 * separate widget for image fields, this class is used to supplant the existing
 * widget that comes with the core image module.
 *
 * @see focal_point_field_widget_form_alter
 */
class FocalPointImageWidget extends ImageWidget {

  /**
   * {@inheritDocs}
   *
   * Form API callback: Processes a image_fp field element.
   *
   * Expands the image_fp type to include the focal_point field.
   *
   * This method is assigned as a #process callback in formElement() method.
   */
  public static function process($element, FormStateInterface $form_state, $form) {
    $element = parent::process($element, $form_state, $form);

    $item = $element['#value'];
    $item['fids'] = $element['fids']['#value'];
    $element_selector = 'focal-point-' . implode('-', $element['#parents']);

    // Add the focal point indicator to preview.
    if (isset($element['preview'])) {
      $preview = array(
        'indicator' => array(
          '#theme_wrappers' => array('container'),
          '#attributes' => array(
            'class' => array('focal-point-indicator'),
            'data-selector' => $element_selector,
            'data-delta' => $element['#delta'],
          ),
          '#markup' => '',
        ),
        'thumbnail' => $element['preview'],
      );

      // Use the existing preview weight value so that the focal point indicator
      // and thumbnail appear in the correct order.
      $preview['#weight'] = isset($element['preview']['#weight']) ? $element['preview']['#weight'] : 0;
      unset($preview['thumbnail']['#weight']);

      $element['preview'] = $preview;
    }

    // Add the focal point field.
    $element_selector = 'focal-point-' . implode('-', $element['#parents']);
    $element['focal_point'] = array(
      '#type' => 'textfield',
      '#title' => 'Focal point',
      '#description' => t('Specify the focus of this image in the form "leftoffset,topoffset" where offsets are in percents. Ex: 25,75'),
      '#default_value' => isset($item['focal_point']) ? $item['focal_point'] : FocalPoint::DEFAULT_VALUE,
      '#element_validate' => array('\Drupal\focal_point\Plugin\Field\FieldWidget\FocalPointImageWidget::validateFocalPoint'),
      '#attributes' => array(
        'class' => array('focal-point', $element_selector),
        'data-selector' => $element_selector,
        'data-field-name' => $element['#field_name'],
      ),
      '#attached' => array(
        'library' => array('focal_point/drupal.focal_point'),
      ),
    );

    return $element;
  }

  /**
   * {@inheritDocs}
   *
   * Form API callback. Retrieves the value for the file_generic field element.
   *
   * This method is assigned as a #value_callback in formElement() method.
   */
  public static function value($element, $input = FALSE, FormStateInterface $form_state) {
    $return = parent::value($element, $input, $form_state);

    // When an element is loaded, focal_point needs to be set. During a form
    // submission the value will already be there.
    if (isset($return['target_id']) && !isset($return['focal_point'])) {
      $element['#focal_point'] = new FocalPoint($return['target_id']);
      $return['focal_point'] = $element['#focal_point']->getFocalPoint();
    }
    return $return;
  }

  /**
   * {@inheritDocs}
   *
   * Validate callback for the focal point field.
   */
  public static function validateFocalPoint($element, FormStateInterface $form_state) {
    $field_name = array_pop($element['#parents']);
    $focal_point_value = $form_state->getValue($field_name);

    if (!is_null($focal_point_value) && !FocalPoint::validate($focal_point_value)) {
      \Drupal::formBuilder()->setError($element, $form_state, t('The !title field should be in the form "leftoffset,topoffset" where offsets are in percents. Ex: 25,75.', array('!title' => $element['#title'])));
    }
  }

}
