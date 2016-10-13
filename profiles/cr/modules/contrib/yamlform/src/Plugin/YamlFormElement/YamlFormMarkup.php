<?php

namespace Drupal\yamlform\Plugin\YamlFormElement;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Mail\MailFormatHelper;
use Drupal\yamlform\YamlFormElementBase;
use Drupal\yamlform\YamlFormSubmissionInterface;

/**
 * Provides a 'yamlform_markup' element.
 *
 * @YamlFormElement(
 *   id = "yamlform_markup",
 *   label = @Translation("HTML markup"),
 *   category = @Translation("Markup element"),
 *   states_wrapper = TRUE,
 * )
 */
class YamlFormMarkup extends YamlFormElementBase {

  /**
   * {@inheritdoc}
   */
  public function isInput(array $element) {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function isContainer(array $element) {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultProperties() {
    return [
      'markup' => '',
      'display_on' => 'form',
      'private' => FALSE,
      'flex' => 1,
      'states' => [],
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
  public function buildHtml(array &$element, $value, array $options = []) {
    // Hide markup element if it should be only displayed on a 'form'.
    if (empty($element['#display_on']) || $element['#display_on'] == 'form') {
      return [];
    }

    // Since we are not passing this element to the
    // yamlform_container_base_html template we need to replace the default
    // sub elements with the value (ie renderable sub elements).
    if (is_array($value)) {
      $element = $value + $element;
    }

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function buildText(array &$element, $value, array $options = []) {
    // Hide markup element if it should be only displayed on a 'form'.
    if (empty($element['#display_on']) || $element['#display_on'] == 'form') {
      return [];
    }

    $element['#markup'] = MailFormatHelper::htmlToText($element['#markup']);

    // Must remove #prefix and #suffix.
    unset($element['#prefix'], $element['#suffix']);

    // Since we are not passing this element to the
    // yamlform_container_base_text template we need to replace the default
    // sub elements with the value (ie renderable sub elements).
    if (is_array($value)) {
      $element = $value + $element;
    }

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function getElementSelectorOptions(array $element) {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    $form['markup'] = [
      '#type' => 'details',
      '#title' => $this->t('Markup settings'),
      '#open' => FALSE,
    ];
    $form['markup']['display_on'] = [
      '#type' => 'select',
      '#title' => $this->t('Display on'),
      '#options' => [
        'form' => t('form only'),
        'display' => t('viewed submission only'),
        'both' => t('both form and viewed submission'),
      ],
    ];
    return $form;
  }

}
