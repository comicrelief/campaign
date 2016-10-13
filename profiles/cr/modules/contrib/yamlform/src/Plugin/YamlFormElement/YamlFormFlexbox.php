<?php

namespace Drupal\yamlform\Plugin\YamlFormElement;

use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a 'flexbox' element.
 *
 * @YamlFormElement(
 *   id = "yamlform_flexbox",
 *   label = @Translation("Flexbox layout"),
 *   category = @Translation("Containers"),
 *   states_wrapper = TRUE,
 * )
 */
class YamlFormFlexbox extends Container {

  /**
   * {@inheritdoc}
   */
  public function getDefaultProperties() {
    return parent::getDefaultProperties() + [
      'align_items' => 'flex-start',
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function build($format, array &$element, $value, array $options = []) {
    return $value;
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    $form['flexbox'] = [
      '#type' => 'details',
      '#title' => $this->t('Flexbox settings'),
      '#open' => FALSE,
    ];
    $form['flexbox']['align_items'] = [
      '#type' => 'select',
      '#title' => $this->t('Align items'),
      '#options' => [
        'flex-start' => $this->t('Top (flex-start)'),
        'flex-end' => $this->t('Bottom (flex-end)'),
        'center' => $this->t('Center (center)'),
      ],
      '#required' => TRUE,
    ];
    return $form;
  }

}
