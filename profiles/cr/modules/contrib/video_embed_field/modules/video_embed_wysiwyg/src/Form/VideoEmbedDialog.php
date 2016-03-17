<?php

/**
 * @file
 * Contains \Drupal\video_embed_wysiwyg\Form\VideoEmbedDialog.
 */

namespace Drupal\video_embed_wysiwyg\Form;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\CloseModalDialogCommand;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormState;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\editor\Ajax\EditorDialogSave;
use Drupal\field\Entity\FieldConfig;
use Drupal\filter\Entity\FilterFormat;
use Drupal\image\Entity\ImageStyle;
use Drupal\video_embed_field\Plugin\Field\FieldFormatter\Video;
use Drupal\video_embed_field\Plugin\Field\FieldWidget\VideoTextfield;
use Drupal\video_embed_field\ProviderManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * A class for a video embed dialog.
 */
class VideoEmbedDialog extends FormBase {

  /**
   * The video provider manager.
   *
   * @var \Drupal\video_embed_field\ProviderManager
   */
  protected $providerManager;

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, FilterFormat $filter_format = NULL) {
    // Add AJAX support.
    $form['#prefix'] = '<div id="video-embed-dialog-form">';
    $form['#suffix'] = '</div>';
    // Ensure relevant dialog libraries are attached.
    $form['#attached']['library'][] = 'editor/drupal.editor.dialog';
    // Simple URL field and submit button for video URL.
    $form['video_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Video URL'),
      '#required' => TRUE,
      '#default_value' => $this->getUserInput($form_state, 'video_url'),
    ];

    // Create a settings form from the existing video formatter.
    $form['settings'] = $this->getVideoFormatterInstance($this->getUserInput($form_state, 'settings'))->settingsForm([], new FormState());;
    $form['settings']['#type'] = 'fieldset';
    $form['settings']['#title'] = $this->t('Settings');

    $form['actions'] = [
      '#type' => 'actions',
    ];
    $form['actions']['save_modal'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
      '#submit' => [],
      '#ajax' => [
        'callback' => '::ajaxSubmit',
        'event' => 'click',
        'wrapper' => 'video-embed-dialog-form',
      ],
    ];
    return $form;
  }

  /**
   * Get an instance of the Video field formatter plugin.
   *
   * This is useful because there is a lot of overlap to the configuration and
   * display of a video in a WYSIWYG and configuring a field formatter. We
   * get an instance of the plugin with our own WYSIWYG settings shimmed in,
   * as well as a fake field_definition because one in this context doesn't
   * exist. This allows us to reuse aspects such as the form and settings
   * summary for the WYSIWYG integration.
   *
   * @param array $settings
   *   The settings to pass to the plugin.
   *
   * @return \Drupal\Core\Field\FormatterInterface
   *   The formatter plugin.
   */
  protected function getVideoFormatterInstance($settings = []) {
    return $this->formatterManager->createInstance('video_embed_field_video', [
      'settings' => !empty($settings) ? $settings : [],
      'third_party_settings' => [],
      'field_definition' => new FieldConfig(['field_name' => 'mock', 'entity_type' => 'mock', 'bundle' => 'mock']),
      'label' => '',
      'view_mode' => '',
    ]);
  }

  /**
   * Get a value from the widget in the WYSIWYG.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state to extract values from.
   * @param string $key
   *   The key to get from the selected WYSIWYG element.
   *
   * @return string
   *   The default value.
   */
  protected function getUserInput(FormStateInterface $form_state, $key) {
    return isset($form_state->getUserInput()['editor_object'][$key]) ? $form_state->getUserInput()['editor_object'][$key] : '';
  }

  /**
   * Get the values from the form and provider required for the client.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state from the dialog submission.
   * @param \Drupal\video_embed_field\ProviderPluginInterface $provider
   *   The provider loaded from the user input.
   *
   * @return array
   *   An array of values sent to the client for use in the WYSIWYG.
   */
  protected function getClientValues(FormStateInterface $form_state, $provider) {
    // @todo Render the thumbnail to download it from the remote. Consider
    // making the download method public. https://www.drupal.org/node/2687077
    $provider->renderThumbnail(FALSE, FALSE);

    // All settings from the field formatter exist in the form and are relevant
    // for the rendering of the video.
    $video_formatter_settings = Video::defaultSettings();
    foreach ($video_formatter_settings as $key => $default) {
      $video_formatter_settings[$key] = $form_state->getValue($key);
    }

    return [
      'preview_thumbnail' => ImageStyle::load('video_embed_wysiwyg_preview')->buildUrl($provider->getLocalThumbnailUri()),
      'video_url' => $form_state->getValue('video_url'),
      'settings' => $video_formatter_settings,
      'settings_summary' => $this->getVideoFormatterInstance($video_formatter_settings)->settingsSummary(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $provider = $this->getProvider($form_state->getValue('video_url'));
    // Display an error if no provider can be loaded for this video.
    if (FALSE == $provider) {
      $form_state->setError($form['video_url'], VideoTextfield::getProviderErrorMessage());
      return;
    }
  }

  /**
   * An AJAX submit callback to validate the WYSIWYG modal.
   */
  public function ajaxSubmit(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    if (!$form_state->getErrors()) {
      // Load the provider and get the information needed for the client.
      $provider = $this->getProvider($form_state->getValue('video_url'));
      $response->addCommand(new EditorDialogSave($this->getClientValues($form_state, $provider)));
      $response->addCommand(new CloseModalDialogCommand());
    }
    else {
      unset($form['#prefix'], $form['#suffix']);
      $form['status_messages'] = [
        '#type' => 'status_messages',
        '#weight' => -10,
      ];
      $response->addCommand(new HtmlCommand(NULL, $form));
    }
    return $response;
  }

  /**
   * Get a provider from some input.
   *
   * @param string $input
   *   The input string.
   *
   * @return bool|\Drupal\video_embed_field\ProviderPluginInterface
   *   A video provider or FALSE on failure.
   */
  protected function getProvider($input) {
    return $this->providerManager->loadProviderFromInput($input);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // The AJAX commands were already added in the AJAX callback. Do nothing in
    // the submit form.
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'video_embed_dialog';
  }

  /**
   * VideoEmbedDialog constructor.
   *
   * @param \Drupal\video_embed_field\ProviderManager $provider_manager
   *   The video provider plugin manager.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer.
   * @param \Drupal\Component\Plugin\PluginManagerInterface $formatter_manager
   *   The field formatter manager.
   */
  public function __construct(ProviderManager $provider_manager, RendererInterface $renderer, PluginManagerInterface $formatter_manager) {
    $this->providerManager = $provider_manager;
    $this->render = $renderer;
    $this->formatterManager = $formatter_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('video_embed_field.provider_manager'), $container->get('renderer'), $container->get('plugin.manager.field.formatter'));
  }

}
