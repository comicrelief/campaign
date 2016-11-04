<?php

namespace Drupal\ds\Plugin\DsField;

use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin that renders a title.
 */
abstract class Title extends Field {

  /**
   * {@inheritdoc}
   */
  public function settingsForm($form, FormStateInterface $form_state) {
    $config = $this->getConfiguration();

    $settings['link'] = array(
      '#type' => 'checkbox',
      '#title' => 'Link',
      '#default_value' => $config['link'],
    );
    $settings['wrapper'] = array(
      '#type' => 'textfield',
      '#title' => 'Wrapper',
      '#default_value' => $config['wrapper'],
      '#description' => $this->t('Eg: h1, h2, p'),
    );
    $settings['class'] = array(
      '#type' => 'textfield',
      '#title' => 'Class',
      '#default_value' => $config['class'],
      '#description' => $this->t('Put a class on the wrapper. Eg: block-title'),
    );

    return $settings;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary($settings) {
    $config = $this->getConfiguration();

    $summary = array();
    if (!empty($config['link'])) {
      $summary[] = 'Link: yes';
    }
    else {
      $summary[] = 'Link: no';
    }

    $summary[] = 'Wrapper: ' . $config['wrapper'];

    if (!empty($config['class'])) {
      $summary[] = 'Class: ' . $config['class'];
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {

    $configuration = array(
      'link' => 0,
      'wrapper' => 'h2',
      'class' => '',
    );

    return $configuration;
  }

  /**
   * {@inheritdoc}
   */
  protected function entityRenderKey() {
    return 'title';
  }

}
