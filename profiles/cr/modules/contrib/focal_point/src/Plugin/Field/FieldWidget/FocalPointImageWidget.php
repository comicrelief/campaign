<?php

/**
 * @file
 * Contains \Drupal\focal_point\Plugin\Field\FieldWidget\FocalPointImageWidget.
 */

namespace Drupal\focal_point\Plugin\Field\FieldWidget;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\crop\Entity\Crop;
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
      '#description' => new TranslatableMarkup('Specify the focus of this image in the form "leftoffset,topoffset" where offsets are in percents. Ex: 25,75'),
      '#default_value' => isset($item['focal_point']) ? $item['focal_point'] : \Drupal::config('focal_point.settings')->get('default_value'),
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
      /** @var \Drupal\file\FileInterface $file */
      $file = \Drupal::service('entity_type.manager')
        ->getStorage('file')
        ->load($return['target_id']);
      $crop_type = \Drupal::config('focal_point.settings')->get('crop_type');
      $crop = Crop::findCrop($file->getFileUri(), $crop_type);
      if ($crop) {
        $anchor = \Drupal::service('focal_point.manager')
          ->absoluteToRelative($crop->x->value, $crop->y->value, $return['width'], $return['height']);
        $return['focal_point'] = "{$anchor['x']},{$anchor['y']}";
      }
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

    if (!is_null($focal_point_value) && \Drupal::service('focal_point.manager')->validateFocalPoint($focal_point_value)) {
      $form_state->setError($element, new TranslatableMarkup('The !title field should be in the form "leftoffset,topoffset" where offsets are in percents. Ex: 25,75.', array('!title' => $element['#title'])));
    }
  }

}
