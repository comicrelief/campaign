<?php
/**
 * Provides an AMP Google Adsense block
 *
 * @Block(
 *   id = "amp_google_adsense_block",
 *   admin_label = @Translation("AMP Google Adsense block"),
 * )
 */

namespace Drupal\amp\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;

class AmpGoogleAdsenseBlock extends BlockBase {
  /**
   * {@inheritdoc}
   */
  public function build() {

    // Start by getting global Adsense configuration.
    $amp_config = \Drupal::config('amp.settings');
    $adsense_id = $amp_config->get('google_adsense_id');
    if (empty($adsense_id)) {
      return array(
        '#markup' => $this->t('This block requires a Google Adsense ID.')
      );
    }

    // Retrieve existing configuration for this block.
    $config = $this->getConfiguration();
    $data_ad_slot = $config['data_ad_slot'];
    $height = $config['height'];
    $width = $config['width'];

    return [
      'inside' => [
        '#theme' => 'amp_ad',
        '#type' => 'adsense',
        '#attributes' => [
          'height' => $height,
          'width' => $width,
          'data-ad-client' => $adsense_id,
          'data-ad-slot' => $data_ad_slot
        ]
      ]
    ];

  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);

    // Retrieve existing configuration for this block.
    $config = $this->getConfiguration();

    $form['width'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Width'),
      '#default_value' => $config['width'],
      '#maxlength' => 25,
      '#size' => 20,
    );
    $form['height'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Height'),
      '#default_value' => $config['height'],
      '#maxlength' => 25,
      '#size' => 20,
    );
    $form['data_ad_slot'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Data ad slot'),
      '#default_value' => $config['data_ad_slot'],
      '#maxlength' => 25,
      '#size' => 20,
    );
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->setConfigurationValue('width', $form_state->getValue('width'));
    $this->setConfigurationValue('height', $form_state->getValue('height'));
    $this->setConfigurationValue('data_ad_slot', $form_state->getValue('data_ad_slot'));
  }
}
