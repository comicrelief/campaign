<?php

/**
 * @file
 * Contains \Drupal\ds\Plugin\DsFieldTemplate\Minimal.
 */

namespace Drupal\ds\Plugin\DsFieldTemplate;

/**
 * Plugin for the reset field template.
 *
 * @DsFieldTemplate(
 *   id = "reset",
 *   title = @Translation("Full reset"),
 *   theme = "ds_field_reset",
 * )
 */
class Reset extends DsFieldTemplateBase {

  /**
   * {@inheritdoc}
   */
  public function alterForm(&$form) {
    $config = $this->getConfiguration();

    $form['lb'] = array(
      '#type' => 'textfield',
      '#title' => t('Label'),
      '#size' => '10',
      '#default_value' => $config['lb'],
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    $config = array();
    $config['lb'] = '';

    return $config;
  }

  /**
   * {@inheritdoc}
   */
  public function massageRenderValues(&$field_settings, $values) {
    if (!empty($values['lb'])) {
      $field_settings['lb'] = $values['lb'];
    }
  }

}
