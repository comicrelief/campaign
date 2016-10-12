<?php

namespace Drupal\yamlform\Plugin\YamlFormElement;

use Drupal\Core\Form\FormStateInterface;
use Drupal\yamlform\YamlFormElementBase;
use Drupal\yamlform\YamlFormInterface;

/**
 * Provides a 'yamlform_codemirror' element.
 *
 * @YamlFormElement(
 *   id = "yamlform_codemirror",
 *   label = @Translation("CodeMirror"),
 *   category = @Translation("Advanced elements"),
 *   multiline = TRUE,
 * )
 */
class YamlFormCodeMirror extends YamlFormElementBase {

  /**
   * {@inheritdoc}
   */
  public function getDefaultProperties() {
    return parent::getDefaultProperties() + [
      'mode' => 'text',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function formatHtml(array &$element, $value, array $options = []) {
    if (empty($value)) {
      return '';
    }

    $format = $this->getFormat($element);
    switch ($format) {
      case 'code':
        return [
          '#theme' => 'yamlform_codemirror',
          '#code' => $value,
          '#type' => $element['#mode'],
        ];

      default:
        return parent::formatHtml($element, $value, $options);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultFormat() {
    return 'code';
  }

  /**
   * {@inheritdoc}
   */
  public function getFormats() {
    return parent::getFormats() + [
      'code' => $this->t('Code'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getTestValue(array $element, YamlFormInterface $yamlform) {
    switch ($element['#mode']) {
      case 'html':
        return '<p><b>Hello World!!!</b></p>';

      case 'yaml':
        return "message: 'Hello World'";

      case 'text':
        return "Hello World";

      default:
        return '';

    }

  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    $form['codemirror'] = [
      '#type' => 'details',
      '#title' => $this->t('CodeMirror settings'),
      '#open' => FALSE,
    ];
    $form['codemirror']['mode'] = [
      '#title' => $this->t('Mode'),
      '#type' => 'select',
      '#options' => [
        'yaml' => $this->t('YAML'),
        'html' => $this->t('HTML'),
        'text' => $this->t('Plain text'),
      ],
      '#required' => TRUE,
    ];
    return $form;
  }

}
