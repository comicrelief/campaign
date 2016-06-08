<?php

/**
 * @file
 * Contains \Drupal\youtube\Plugin\Field\FieldFormatter\YouTubeFormatter.
 */

namespace Drupal\youtube\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'youtube_video' formatter.
 *
 * @FieldFormatter(
 *   id = "youtube_video",
 *   label = @Translation("YouTube video"),
 *   field_types = {
 *     "youtube"
 *   }
 * )
 */
class YouTubeFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return array(
      'youtube_size' => '450x315',
      'youtube_width' => '',
      'youtube_height' => '',
      'youtube_autoplay' => '',
      'youtube_loop' => '',
      'youtube_showinfo' => '',
      'youtube_controls' => '',
      'youtube_autohide' => '',
      'youtube_iv_load_policy' => '',
    ) + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = parent::settingsForm($form, $form_state);

    $elements['youtube_size'] = array(
      '#type' => 'select',
      '#title' => t('YouTube video size'),
      '#options' => youtube_size_options(),
      '#default_value' => $this->getSetting('youtube_size'),
    );
    $elements['youtube_width'] = array(
      '#type' => 'textfield',
      '#title' => t('Width'),
      '#size' => 10,
      '#default_value' => $this->getSetting('youtube_width'),
      '#states' => array(
        'visible' => array(
          ':input[name*="youtube_size"]' => array('value' => 'custom'),
        ),
      ),
    );
    $elements['youtube_height'] = array(
      '#type' => 'textfield',
      '#title' => t('Height'),
      '#size' => 10,
      '#default_value' => $this->getSetting('youtube_height'),
      '#states' => array(
        'visible' => array(
          ':input[name*="youtube_size"]' => array('value' => 'custom'),
        ),
      ),
    );
    $elements['youtube_autoplay'] = array(
      '#type' => 'checkbox',
      '#title' => t('Play video automatically when loaded (autoplay).'),
      '#default_value' => $this->getSetting('youtube_autoplay'),
    );
    $elements['youtube_loop'] = array(
      '#type' => 'checkbox',
      '#title' => t('Loop the playback of the video (loop).'),
      '#default_value' => $this->getSetting('youtube_loop'),
    );
    $elements['youtube_showinfo'] = array(
      '#type' => 'checkbox',
      '#title' => t('Hide video title and uploader info (showinfo).'),
      '#default_value' => $this->getSetting('youtube_showinfo'),
    );
    $elements['youtube_controls'] = array(
      '#type' => 'checkbox',
      '#title' => t('Always hide video controls (controls).'),
      '#default_value' => $this->getSetting('youtube_controls'),
    );
    $elements['youtube_autohide'] = array(
      '#type' => 'checkbox',
      '#title' => t('Hide video controls after play begins (autohide).'),
      '#default_value' => $this->getSetting('youtube_autohide'),
    );
    $elements['youtube_iv_load_policy'] = array(
      '#type' => 'checkbox',
      '#title' => t('Hide video annotations by default (iv_load_policy).'),
      '#default_value' => $this->getSetting('youtube_iv_load_policy'),
    );
    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = array();
    $cp = "";
    $youtube_size = $this->getSetting('youtube_size');

    $parameters = array(
      $this->getSetting('youtube_autoplay'),
      $this->getSetting('youtube_loop'),
      $this->getSetting('youtube_showinfo'),
      $this->getSetting('youtube_controls'),
      $this->getSetting('youtube_autohide'),
      $this->getSetting('youtube_iv_load_policy'),
    );

    foreach ($parameters as $parameter) {
      if ($parameter) {
        $cp = t(', custom parameters');
        break;
      }
    }
    $summary[] = t('YouTube video: @youtube_size@cp', array('@youtube_size' => $youtube_size, '@cp' => $cp));
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function prepareView(array $entities_items) {}

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = array();
    $settings = $this->getSettings();

    foreach ($items as $delta => $item) {
      $element[$delta] = array(
        '#theme' => 'youtube_video',
        '#video_id' => $item->video_id,
        '#entity_title' => $items->getEntity()->label(),
        '#settings' => $settings,
      );

      if ($settings['youtube_size'] == 'responsive') {
        $element[$delta]['#attached']['library'][] = 'youtube/drupal.youtube.responsive';
      }
    }
    return $element;
  }

}
