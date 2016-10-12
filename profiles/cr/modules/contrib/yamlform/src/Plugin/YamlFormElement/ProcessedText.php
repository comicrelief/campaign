<?php

namespace Drupal\yamlform\Plugin\YamlFormElement;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Mail\MailFormatHelper;
use Drupal\yamlform\YamlFormSubmissionInterface;

/**
 * Provides a 'processed_text' element.
 *
 * @YamlFormElement(
 *   id = "processed_text",
 *   label = @Translation("Processed text"),
 *   category = @Translation("Markup elements"),
 *   states_wrapper = TRUE,
 * )
 */
class ProcessedText extends YamlFormMarkup {

  /**
   * {@inheritdoc}
   */
  public function getDefaultProperties() {
    return [
      'text' => '',
      'format' => filter_default_format(\Drupal::currentUser()),
      'display_on' => 'form',
      'private' => FALSE,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function prepare(array &$element, YamlFormSubmissionInterface $yamlform_submission) {
    parent::prepare($element, $yamlform_submission);

    // Hide markup element is it should be only displayed on 'view'.
    if (isset($element['#display_on']) && $element['#display_on'] == 'view') {
      $element['#access'] = FALSE;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function buildText(array &$element, $value, array $options = []) {
    // Hide markup element if it should be only displayed on a 'form'.
    if (empty($element['#display_on']) || $element['#display_on'] == 'form') {
      return [];
    }

    // Copy to element so that we can render it without altering the actual
    // $element.
    $render_element = $element;
    $html = (string) \Drupal::service('renderer')->renderPlain($render_element);
    $element['#markup'] = MailFormatHelper::htmlToText($html);

    // Must remove #type, #text, and #format.
    unset($element['#type'], $element['#text'], $element['#format']);

    // Must remove #prefix and #suffix.
    unset($element['#prefix'], $element['#suffix']);

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    $form['markup']['text'] = [
      '#type' => 'text_format',
      '#format' => '',
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function setConfigurationFormDefaultValue(array &$form, array &$element, array &$property_element, $property_name) {
    // Move get the processed_text element's #format apply it the text_format
    // element.
    if ($property_name == 'format') {
      $form['markup']['text']['#format'] = $element['format'];
    }

    parent::setConfigurationFormDefaultValue($form, $element, $property_element, $property_name);
  }

  /**
   * {@inheritdoc}
   */
  protected function getConfigurationFormProperty(array &$properties, $property_name, $property_value, array $element) {
    if ($property_name == 'text') {
      $properties['text'] = $property_value['value'];
      $properties['format'] = $property_value['format'];
    }
    else {
      parent::getConfigurationFormProperty($properties, $property_name, $property_value, $element);
    }
  }

}
