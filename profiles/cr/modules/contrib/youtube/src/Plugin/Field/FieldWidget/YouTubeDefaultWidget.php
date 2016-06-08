<?php

/**
 * @file
 * Contains \Drupal\youtube\Plugin\Field\FieldWidget\YouTubeDefaultWidget.
 */

namespace Drupal\youtube\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'youtube_default' widget.
 *
 * @FieldWidget(
 *   id = "youtube",
 *   label = @Translation("YouTube video widget"),
 *   field_types = {
 *     "youtube"
 *   },
 *   settings = {
 *     "placeholder_url" = ""
 *   }
 * )
 */
class YouTubeDefaultWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = parent::settingsForm($form, $form_state);

    $elements['placeholder_url'] = array(
      '#type' => 'textfield',
      '#title' => t('Placeholder for URL'),
      '#default_value' => $this->getSetting('placeholder_url'),
      '#description' => t('Text that will be shown inside the field until a value is entered. This hint is usually a sample value or a brief description of the expected format.'),
    );

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = array();

    $placeholder_url = $this->getSetting('placeholder_url');
    if (empty($placeholder_url)) {
      $summary[] = t('No placeholders');
    }
    else {
      if (!empty($placeholder_url)) {
        $summary[] = t('URL placeholder: @placeholder_url', array('@placeholder_url' => $placeholder_url));
      }
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element['input'] = $element + array(
      '#type' => 'textfield',
      '#placeholder' => $this->getSetting('placeholder_url'),
      '#default_value' => isset($items[$delta]->input) ? $items[$delta]->input : NULL,
      '#maxlength' => 255,
      '#element_validate' => array(array($this, 'validateInput')),
    );

    if ($element['input']['#description'] == '') {
      $element['input']['#description'] = t('Enter the YouTube URL. Valid URL
      formats include: http://www.youtube.com/watch?v=1SqBdS0XkV4 and
      http://youtu.be/1SqBdS0XkV4');
    }

    if (isset($items->get($delta)->video_id)) {
      $element['video_id'] = array(
        '#prefix' => '<div class="youtube-video-id">',
        '#markup' => t('YouTube video ID: @video_id', array('@video_id' => $items->get($delta)->video_id)),
        '#suffix' => '</div>',
        '#weight' => 1,
      );
    }
    return $element;
  }

  /**
   * Validate video URL.
   */
  public function validateInput(&$element, FormStateInterface &$form_state, $form) {
    $input = $element['#value'];
    $video_id = youtube_get_video_id($input);

    if ($video_id && strlen($video_id) <= 20) {
      $video_id_element = array(
        '#parents' => $element['#parents'],
      );
      array_pop($video_id_element['#parents']);
      $video_id_element['#parents'][] = 'video_id';
      $form_state->setValueForElement($video_id_element, $video_id);
    }
    elseif (!empty($input)) {
      $form_state->setError($element, t('Please provide a valid YouTube URL.'));
    }
  }

}
