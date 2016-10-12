<?php

namespace Drupal\yamlform\Plugin\YamlFormElement;

use Drupal\Component\Utility\Unicode;
use Drupal\Core\Form\FormStateInterface;
use Drupal\yamlform\YamlFormElementBase;
use Drupal\yamlform\YamlFormSubmissionInterface;

/**
 * Provides a base 'text' (field) class.
 */
abstract class TextBase extends YamlFormElementBase {

  /**
   * {@inheritdoc}
   */
  public function getDefaultProperties() {
    return parent::getDefaultProperties() + [
      'size' => '',
      'maxlength' => '',
      'placeholder' => '',
      'pattern' => '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function prepare(array &$element, YamlFormSubmissionInterface $yamlform_submission) {
    parent::prepare($element, $yamlform_submission);

    // Counter.
    if (!empty($element['#counter_type']) && !empty($element['#counter_maximum'])) {
      $element['#attributes']['data-counter-type'] = $element['#counter_type'];
      $element['#attributes']['data-counter-limit'] = $element['#counter_maximum'];
      if (!empty($element['#counter_message'])) {
        $element['#attributes']['data-counter-message'] = $element['#counter_message'];
      }

      $element['#attributes']['class'][] = 'js-yamlform-counter';
      $element['#attributes']['class'][] = 'yamlform-counter';
      $element['#attached']['library'][] = 'yamlform/yamlform.element.counter';

      $element['#element_validate'][] = [get_class($this), 'validateCounter'];
    }

    // Input mask.
    if (!empty($element['#input_mask'])) {
      // See if the element mask is JSON by looking for 'name':, else assume it
      // is a mask pattern.
      $input_mask = $element['#input_mask'];
      if (preg_match("/^'[^']+'\s*:/", $input_mask)) {
        $element['#attributes']['data-inputmask'] = $input_mask;
      }
      else {
        $element['#attributes']['data-inputmask-mask'] = $input_mask;
      }

      $element['#attributes']['class'][] = 'js-yamlform-element-mask';
      $element['#attached']['library'][] = 'yamlform/yamlform.element.inputmask';
    }
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    // Input mask.
    $form['form']['input_mask'] = [
      '#type' => 'yamlform_select_other',
      '#title' => $this->t('Input masks'),
      '#description' => $this->t('An <a href=":href">inputmask</a> helps the user with the element by ensuring a predefined format.', [':href' => 'https://github.com/RobinHerbots/jquery.inputmask']),
      '#other__option_label' => $this->t('Custom...'),
      '#other__placeholder' => $this->t('Enter input mask...'),
      '#other__description' => $this->t('(9 = numeric; a = alphabetical; * = alphanumeric)'),
      '#options' => [
        '' => '',
        'Basic' => [
          "'alias': 'currency'" => $this->t('Currency - @format', ['@format' => '$ 9.99']),
          "'alias': 'mm/dd/yyyy'" => $this->t('Date - @format', ['@format' => 'mm/dd/yyyy']),
          "'alias': 'email'" => $this->t('Email - @format', ['@format' => 'example@example.com']),
          "'alias': 'percentage'" => $this->t('Percentage - @format', ['@format' => '99%']),
          '(999) 999-9999' => $this->t('Phone - @format', ['@format' => '(999) 999-9999']),
          '99999[-9999]' => $this->t('Zip code - @format', ['@format' => '99999[-9999]']),
        ],
        'Advanced' => [
          "'alias': 'ip'" => 'IP address - 255.255.255.255',
          '[9-]AAA-999' => 'License plate - [9-]AAA-999',
          "'alias': 'mac'" => 'MAC addresses - 99-99-99-99-99-99',
          '999-99-9999' => 'SSN - 999-99-9999',
          "'alias': 'vin'" => 'VIN (Vehicle identification number)',
        ],
      ],
    ];

    // Pattern.
    $form['validation']['pattern'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Pattern'),
      '#description' => $this->t('A <a href=":href">regular expression</a> that the element\'s value is checked against.', [':href' => 'http://www.w3schools.com/js/js_regexp.asp']),
    ];

    // Counter.
    $form['validation']['counter_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Count'),
      '#description' => $this->t('Limit entered value to a maximum number of characters or words.'),
      '#options' => [
        '' => '',
        'character' => $this->t('Characters'),
        'word' => $this->t('Words'),
      ],
    ];
    $form['validation']['counter_maximum'] = [
      '#type' => 'number',
      '#title' => $this->t('Count maximum'),
      '#min' => 1,
      '#states' => [
        'invisible' => [
          ':input[name="properties[counter_type]"]' => ['value' => ''],
        ],
        'optional' => [
          ':input[name="properties[counter_type]"]' => ['value' => ''],
        ],
      ],
    ];
    $form['validation']['counter_message'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Count message'),
      '#description' => $this->t('Defaults to: %value', ['%value' => $this->t('X characters/word(s) left')]),
      '#states' => [
        'invisible' => [
          ':input[name="properties[counter_type]"]' => ['value' => ''],
        ],
      ],
    ];

    return $form;
  }

  /**
   * Form API callback. Validate (word/charcter) counter.
   */
  public static function validateCounter(array &$element, FormStateInterface $form_state) {
    $name = $element['#name'];
    $value = $form_state->getValue($name);
    $type = $element['#counter_type'];
    $limit = $element['#counter_maximum'];

    // Validate character count.
    if ($type == 'character' && Unicode::strlen($value) <= $limit) {
      return;
    }
    // Validate word count.
    elseif ($type == 'word' && str_word_count($value) <= $limit) {
      return;
    }

    // Display error.
    $t_args = [
      '%name' => $name,
      '@limit' => $limit,
      '@type' => ($type == 'character') ? t('characters') : t('words'),
    ];
    $form_state->setError($element, t('%name must be less than @limit @type.', $t_args));
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::validateConfigurationForm($form, $form_state);
    $properties = $this->getConfigurationFormProperties($form, $form_state);

    // Validate #pattern's regular expression.
    // @see \Drupal\Core\Render\Element\FormElement::validatePattern
    // @see http://stackoverflow.com/questions/4440626/how-can-i-validate-regex
    if (!empty($properties['#pattern'])) {
      set_error_handler('_yamlform_entity_element_validate_rendering_error_handler');
      if (preg_match('{^(?:' . $properties['#pattern'] . ')$}', NULL) === FALSE) {
        $form_state->setErrorByName('pattern', t('Pattern %pattern is not a valid regular expression.', ['%pattern' => $properties['#pattern']]));
      }
      set_error_handler('_drupal_error_handler');
    }

    // Validate #counter_maximum.
    if (!empty($properties['#counter_type']) && empty($properties['#counter_maximum'])) {
      $form_state->setErrorByName('counter_maximum', t('Counter maximum is required.'));
    }
  }

}
