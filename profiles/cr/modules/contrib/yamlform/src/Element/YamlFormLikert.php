<?php

namespace Drupal\yamlform\Element;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element\FormElement;

/**
 * Provides a form element for a likert scale.
 *
 * @FormElement("yamlform_likert")
 */
class YamlFormLikert extends FormElement {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = get_class($this);
    return [
      '#input' => TRUE,
      '#process' => [
        [$class, 'processYamlFormLikert'],
        [$class, 'processAjaxForm'],
      ],
      '#theme_wrappers' => ['form_element'],
      '#required' => FALSE,
      '#questions' => [],
      // Using #answers insteads of #options to prevent triggering
      // \Drupal\Core\Form\FormValidator::performRequiredValidation().
      '#answers' => [],
    ];
  }

  /**
   * Processes a likert scale form element.
   */
  public static function processYamlFormLikert(&$element, FormStateInterface $form_state, &$complete_form) {
    // Build header.
    $header = [
      ['question' => FALSE],
    ] + $element['#answers'];

    // Randomize questions.
    if (!empty($element['#questions_randomize'])) {
      shuffle($element['#questions']);
    }

    // Build rows.
    $rows = [];
    foreach ($element['#questions'] as $question_key => $question_title) {
      $value = (isset($element['#value'][$question_key])) ? $element['#value'][$question_key] : NULL;
      $row = [];
      // Must format the label as an item so that inline form errors will be
      // displayed.
      $row['_question_title'] = [
        '#type' => 'item',
        '#title' => $question_title,
        // Must include an empty <span> so that the item's value is
        // not required.
        '#value' => '<span></span>',
        '#required' => $element['#required'],
      ];
      foreach ($element['#answers'] as $answer_key => $answer_title) {
        $row[$answer_key] = [
          '#parents' => [$element['#name'], $question_key],
          '#type' => 'radio',
          '#title' => $answer_title,
          '#title_display' => 'after',
          '#return_value' => $answer_key,
          '#value' => $value,
        ];
      }
      $rows[$question_key] = $row;
    }

    $element['table'] = [
      '#type' => 'table',
      '#header' => $header,
      '#attributes' => [
        'class' => ['yamlform-likert-table'],
        'data-likert-answers-count' => count($element['#answers']),
      ],
    ] + $rows;

    // Build table element with selected properties.
    $properties = [
      '#states',
      '#sticky',
    ];
    $element['table'] += array_intersect_key($element, array_combine($properties, $properties));

    $element['#tree'] = TRUE;
    $element['#element_validate'] = [[get_called_class(), 'validateYamlFormLikert']];
    $element['#attached']['library'][] = 'yamlform/yamlform.element.likert';

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public static function valueCallback(&$element, $input, FormStateInterface $form_state) {
    $default_value = [];
    foreach ($element['#questions'] as $question_key => $question_title) {
      $default_value[$question_key] = '';
    }

    if ($input === FALSE) {
      $element += ['#default_value' => []];
      return $element['#default_value'] + $default_value;
    }
    $value = $default_value;
    foreach ($value as $allowed_key => $default) {
      if (isset($input[$allowed_key]) && is_scalar($input[$allowed_key])) {
        $value[$allowed_key] = (string) $input[$allowed_key];
      }
    }
    return $value;
  }

  /**
   * Validates a likert element.
   */
  public static function validateYamlFormLikert(&$element, FormStateInterface $form_state, &$complete_form) {
    $value = $element['#value'];

    if (!empty($element['#required'])) {
      foreach ($element['#questions'] as $question_key => $question_title) {
        if (empty($value[$question_key])) {
          $form_state->setError($element['table'][$question_key]['_question_title'], t('@name field is required.', ['@name' => $question_title]));
        }
      }
    }

    $form_state->setValueForElement($element, $value);
  }

}
