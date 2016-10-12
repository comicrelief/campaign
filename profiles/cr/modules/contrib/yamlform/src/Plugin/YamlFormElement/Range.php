<?php

namespace Drupal\yamlform\Plugin\YamlFormElement;

use Drupal\Core\Form\FormStateInterface;
use Drupal\yamlform\YamlFormSubmissionInterface;

/**
 * Provides a 'range' element.
 *
 * @YamlFormElement(
 *   id = "range",
 *   api = "https://api.drupal.org/api/drupal/core!lib!Drupal!Core!Render!Element!Range.php/class/Range",
 *   label = @Translation("Range"),
 *   category = @Translation("Advanced elements"),
 * )
 */
class Range extends NumericBase {

  /**
   * {@inheritdoc}
   */
  public function getDefaultProperties() {
    return parent::getDefaultProperties() + [
      'min' => '',
      'max' => '',
      'step' => '',
      'range__output' => FALSE,
      'range__output_prefix' => '',
      'range__output_suffix' => '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function prepare(array &$element, YamlFormSubmissionInterface $yamlform_submission) {
    parent::prepare($element, $yamlform_submission);

    if (!empty($element['#range__output'])) {
      $element['#attributes']['data-range-output'] = 'true';
      $element['#attributes']['class'][] = 'js-form-range-output';
      $element['#attributes']['class'][] = 'form-range-output';
      $element['#wrapper_attributes']['class'][] = 'js-form-type-range-output';
      $element['#wrapper_attributes']['class'][] = 'form-type-range-output';
      if (!empty($element['#range__output_prefix'])) {
        $element['#attributes']['data-range-output-prefix'] = $element['#range__output_prefix'];
      }
      if (!empty($element['#range__output_suffix'])) {
        $element['#attributes']['data-range-output-suffix'] = $element['#range__output_suffix'];
      }

      $element['#attached']['library'][] = 'yamlform/yamlform.element.range';
    }
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $form['range'] = [
      '#type' => 'details',
      '#title' => $this->t('Range settings'),
      '#open' => TRUE,
    ];
    $form['range']['range__output'] = [
      '#type' => 'checkbox',
      '#title' => $this->t("Output the range's value."),
      '#return_type' => TRUE,
    ];
    $form['range']['range__output_prefix'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Range output prefix'),
      '#description' => $this->t('Text or code that is placed directly in front of the output. This can be used to prefix a textfield with a constant string. Examples: $, #, -.'),
      '#size' => 10,
      '#states' => [
        'visible' => [
          ':input[name="properties[range__output]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['range']['range__output_suffix'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Range output suffix'),
      '#description' => $this->t('Text or code that is placed directly after the output. This can be used to add a unit to a textfield. Examples: lb, kg, %.'),
      '#size' => 10,
      '#states' => [
        'visible' => [
          ':input[name="properties[range__output]"]' => ['checked' => TRUE],
        ],
      ],
    ];

    return $form;
  }

}
