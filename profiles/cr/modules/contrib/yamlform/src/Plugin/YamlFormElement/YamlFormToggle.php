<?php

namespace Drupal\yamlform\Plugin\YamlFormElement;

use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a 'toggle' element.
 *
 * @YamlFormElement(
 *   id = "yamlform_toggle",
 *   label = @Translation("Toggle"),
 *   category = @Translation("Advanced elements"),
 * )
 */
class YamlFormToggle extends Checkbox {

  /**
   * {@inheritdoc}
   */
  public function getDefaultProperties() {
    $properties = parent::getDefaultProperties() + [
      'toggle_theme' => 'light',
      'toggle_size' => 'medium',
      'on_text' => '',
      'off_text' => '',
    ];
    $properties['title_display'] = 'after';
    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function formatText(array &$element, $value, array $options = []) {
    $format = $this->getFormat($element);

    switch ($format) {
      case 'value';
        $on_text = (!empty($element['#on_text'])) ? $element['#on_text'] : $this->t('Yes');
        $off_text = (!empty($element['#off_text'])) ? $element['#off_text'] : $this->t('No');
        return ($value) ? $on_text : $off_text;

      case 'raw';
      default:
        return ($value) ? 1 : 0;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    $form['toggle'] = [
      '#type' => 'details',
      '#title' => $this->t('toggle settings'),
      '#open' => FALSE,
    ];
    $form['toggle']['toggle_theme'] = [
      '#type' => 'select',
      '#title' => $this->t('Toggle theme'),
      '#options' => [
        'light' => $this->t('Light'),
        'dark' => $this->t('Dark'),
        'iphone' => $this->t('iPhone'),
        'modern' => $this->t('Modern'),
        'soft' => $this->t('Soft'),
      ],
      '#required' => TRUE,
    ];
    $form['toggle']['toggle_size'] = [
      '#type' => 'select',
      '#title' => $this->t('Toggle size'),
      '#options' => [
        'small' => $this->t('Small (@size)', ['@size' => '16px']),
        'medium' => $this->t('Medium (@size)', ['@size' => '24px']),
        'large' => $this->t('Large (@size)', ['@size' => '32px']),
      ],
      '#required' => TRUE,
    ];
    $form['toggle']['on_text'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Toggle on text'),
    ];
    $form['toggle']['off_text'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Toggle off text'),
    ];
    return $form;
  }

}
