<?php

namespace Drupal\ds\Plugin\DsField\Block;

use Drupal\ds\Plugin\DsField\DsFieldBase;
use Drupal\Component\Utility\Html;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin that renders the title of a block.
 *
 * @DsField(
 *   id = "block_description",
 *   title = @Translation("Description"),
 *   entity_type = "block_content",
 *   provider = "block_content"
 * )
 */
class BlockDescription extends DsFieldBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->getConfiguration();

    $output = $this->entity()->label();

    if (empty($output)) {
      return array();
    }

    $output = Html::escape($output);

    // Wrapper and class.
    if (!empty($config['wrapper'])) {
      $wrapper = Html::escape($config['wrapper']);
      $class = (!empty($config['class'])) ? ' class="' . Html::escape($config['class']) . '"' : '';
      $output = '<' . $wrapper . $class . '>' . $output . '</' . $wrapper . '>';
    }

    return array(
      '#markup' => $output,
    );
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm($form, FormStateInterface $form_state) {
    $config = $this->getConfiguration();

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
      'wrapper' => 'h2',
      'class' => '',
    );

    return $configuration;
  }

  /**
   * {@inheritdoc}
   */
  protected function entityRenderKey() {
    return 'label';
  }

}
