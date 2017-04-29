<?php

/**
 * @file
 * Contains \Drupal\amp\Plugin\Field\FieldFormatter\AmpVideoFormatter.
 */

namespace Drupal\amp\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Plugin\Field\FieldFormatter\GenericFileFormatter;

/**
 * Plugin implementation of the 'amp_video' formatter.
 *
 * @FieldFormatter(
 *   id = "amp_video",
 *   label = @Translation("AMP Video"),
 *   description = @Translation("Display an AMP video file."),
 *   field_types = {
 *     "file"
 *   }
 * )
 */
class AmpVideoFormatter extends GenericFileFormatter {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return array(
      'amp_video_height' => 175,
      'amp_video_width' => 350,
    ) + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element = parent::settingsForm($form, $form_state);

    $element['amp_video_height'] = array(
      '#type' => 'number',
      '#title' => t('Height'),
      '#size' => 10,
      '#default_value' => $this->getSetting('amp_video_height'),
    );

    $element['amp_video_width'] = array(
      '#type' => 'number',
      '#title' => t('Width'),
      '#size' => 10,
      '#default_value' => $this->getSetting('amp_video_width'),
    );

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = parent::settingsSummary();

    $height_setting = $this->getSetting('amp_video_height');
    if (isset($height_setting)) {
      $summary[] = t('Height: @height' . 'px', array('@height' => $height_setting));
    }

    $width_setting = $this->getSetting('amp_video_width');
    if (isset($width_setting)) {
      $summary[] = t('Width: @width' . 'px', array('@width' => $width_setting));
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = parent::viewElements($items, $langcode);

    foreach ($elements as $delta => $element) {
      $elements[$delta]['#theme'] = 'amp_video';
      $elements[$delta]['#attributes']['height'] = $this->getSetting('amp_video_height');
      $elements[$delta]['#attributes']['width'] = $this->getSetting('amp_video_width');
    }

    return $elements;
  }
}
