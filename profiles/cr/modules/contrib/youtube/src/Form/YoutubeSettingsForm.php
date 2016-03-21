<?php

/**
 * @file
 * Contains \Drupal\youtube\Form\YoutubeSettingsForm.
 */

namespace Drupal\youtube\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Site\Settings;
use Drupal\Core\Form\FormStateInterface;


/**
 * Configure Youtube settings for this site.
 */
class YoutubeSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormID() {
    return 'youtube_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['youtube.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->configFactory->get('youtube.settings');
    $form['text'] = array(
      '#type' => 'markup',
      '#markup' => '<p>' . t('The following settings will be used as default
        values on all YouTube video fields.  Many of these settings can be
        overridden on a per-field basis.') . '</p>',
    );
    $form['youtube_global'] = array(
      '#type' => 'fieldset',
      '#title' => t('Video parameters'),
    );
    $form['youtube_global']['youtube_suggest'] = array(
      '#type' => 'checkbox',
      '#title' => t('Show suggested videos when the video finishes'),
      '#default_value' => $config->get('youtube_suggest'),
    );
    $form['youtube_global']['youtube_modestbranding'] = array(
      '#type' => 'checkbox',
      '#title' => t('Do not show YouTube logo on video player control bar
        (modestbranding).'),
      '#default_value' => $config->get('youtube_modestbranding'),
    );
    $form['youtube_global']['youtube_theme'] = array(
      '#type' => 'checkbox',
      '#title' => t('Use a light colored control bar for video player controls
        (theme).'),
      '#default_value' => $config->get('youtube_theme'),
    );
    $form['youtube_global']['youtube_color'] = array(
      '#type' => 'checkbox',
      '#title' => t('Use a white colored video progress bar (color).'),
      '#default_value' => $config->get('youtube_color'),
      '#description' => t('Note: the modestbranding parameter will be ignored
        when this is in use.'),
    );
    $form['youtube_global']['youtube_enablejsapi'] = array(
      '#type' => 'checkbox',
      '#title' => t('Enable use of the IFrame API (enablejsapi, origin).'),
      '#default_value' => $config->get('youtube_enablejsapi'),
      '#description' => t('For more information on the IFrame API and how to use
        it, see the <a href="@api_reference">IFrame API documentation</a>.',
        array(
          '@api_reference' => 'https://developers.google.com/youtube/iframe_api_reference',
        )),
    );
    $form['youtube_global']['youtube_wmode'] = array(
      '#type' => 'checkbox',
      '#title' => t('Fix overlay problem on IE8 and lower'),
      '#default_value' => $config->get('youtube_wmode'),
      '#description' => t('Checking this will fix the issue of a YouTube video
        showing above a modal window (including Drupal\'s Overlay). This is
        needed if you have Overlay users in IE or have modal windows throughout
        your site.'),
    );
    $form['youtube_thumbs'] = array(
      '#type' => 'fieldset',
      '#title' => t('Thumbnails'),
     );
    $form['youtube_thumbs']['youtube_thumb_dir'] = array(
      '#type' => 'textfield',
      '#title' => t('YouTube thumbnail directory'),
      '#field_prefix' => Settings::get('file_public_path', \Drupal::service('kernel')->getSitePath() . '/files') . '/',
      '#field_suffix' => '/thumbnail.jpg',
      '#description' => t('Location, within the files directory, where you would
        like the YouTube thumbnails stored.'),
      '#default_value' => $config->get('youtube_thumb_dir'),
    );
    $form['youtube_thumbs']['youtube_thumb_hires'] = array(
      '#type' => 'checkbox',
      '#title' => t('Save higher resolution thumbnail images'),
      '#description' => t('This will save thumbnails larger than the default
        size, 480x360, to the thumbnails directory specified above.'),
      '#default_value' => $config->get('youtube_thumb_hires'),
    );
    $form['youtube_thumbs']['youtube_thumb_delete_all'] = array(
      '#type' => 'submit',
      '#value' => t('Refresh existing thumbnail image files'),
      '#submit' => array('youtube_thumb_delete_all'),
    );
    $form['youtube_privacy'] = array(
      '#type' => 'checkbox',
      '#title' => t('Enable privacy-enhanced mode'),
      '#default_value' => $config->get('youtube_privacy'),
      '#description' => t('Checking this box will prevent YouTube from setting
        cookies in your site visitors browser.'),
    );
    $form['youtube_player_class'] = array(
      '#type' => 'textfield',
      '#title' => t('YouTube player class'),
      '#default_value' => $config->get('youtube_player_class'),
      '#description' => t('The iframe of every player will be given this class.
        They will also be given IDs based off of this value.'),
    );

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    \Drupal::configFactory()->getEditable('youtube.settings')
      ->set('youtube_suggest', $values['youtube_suggest'])
      ->set('youtube_modestbranding', $values['youtube_modestbranding'])
      ->set('youtube_theme', $values['youtube_theme'])
      ->set('youtube_color', $values['youtube_color'])
      ->set('youtube_enablejsapi', $values['youtube_enablejsapi'])
      ->set('youtube_privacy', $values['youtube_privacy'])
      ->set('youtube_wmode', $values['youtube_wmode'])
      ->set('youtube_player_class', $values['youtube_player_class'])
      ->set('youtube_thumb_dir', $values['youtube_thumb_dir'])
      ->set('youtube_thumb_hires', $values['youtube_thumb_hires'])
      ->save();

    parent::submitForm($form, $form_state);
  }

}
