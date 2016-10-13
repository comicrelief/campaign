<?php

namespace Drupal\yamlform_test\Plugin\YamlFormElement;

use Drupal\Core\Form\FormStateInterface;
use Drupal\yamlform\YamlFormElementBase;
use Drupal\yamlform\YamlFormSubmissionInterface;

/**
 * Provides a 'yamlform_test' element.
 *
 * @YamlFormElement(
 *   id = "yamlform_test",
 *   label = @Translation("Form test"),
 *   description = @Translation("Test form element.")
 * )
 */
class YamlFormTest extends YamlFormElementBase {

  /**
   * {@inheritdoc}
   */
  public function prepare(array &$element, YamlFormSubmissionInterface $yamlform_submission) {
    $this->displayMessage(__FUNCTION__);
    $element['#element_validate'][] = [get_class($this), 'validate'];
  }

  /**
   * {@inheritdoc}
   */
  public function setDefaultValue(array &$element) {
    $this->displayMessage(__FUNCTION__);
  }

  /**
   * {@inheritdoc}
   */
  public function formatHtml(array &$element, $value, array $options = []) {
    $this->displayMessage(__FUNCTION__);
    return '<i>' . $this->formatText($element, $value, $options) . '</i>';
  }

  /**
   * {@inheritdoc}
   */
  public function formatText(array &$element, $value, array $options = []) {
    $this->displayMessage(__FUNCTION__);
    return strtoupper($value);
  }

  /**
   * {@inheritdoc}
   */
  public function preCreate(array &$element, array $values) {
    $this->displayMessage(__FUNCTION__);
  }

  /**
   * {@inheritdoc}
   */
  public function postCreate(array &$element, YamlFormSubmissionInterface $yamlform_submission) {
    $this->displayMessage(__FUNCTION__);
  }

  /**
   * {@inheritdoc}
   */
  public function postLoad(array &$element, YamlFormSubmissionInterface $yamlform_submission) {
    $this->displayMessage(__FUNCTION__);
  }

  /**
   * {@inheritdoc}
   */
  public function preDelete(array &$element, YamlFormSubmissionInterface $yamlform_submission) {
    $this->displayMessage(__FUNCTION__);
  }

  /**
   * {@inheritdoc}
   */
  public function postDelete(array &$element, YamlFormSubmissionInterface $yamlform_submission) {
    $this->displayMessage(__FUNCTION__);
  }

  /**
   * {@inheritdoc}
   */
  public function preSave(array &$element, YamlFormSubmissionInterface $yamlform_submission) {
    $this->displayMessage(__FUNCTION__);
  }

  /**
   * {@inheritdoc}
   */
  public function save(array &$element, YamlFormSubmissionInterface $yamlform_submission) {
    $this->displayMessage(__FUNCTION__);
  }

  /**
   * {@inheritdoc}
   */
  public function postSave(array &$element, YamlFormSubmissionInterface $yamlform_submission, $update = TRUE) {
    $this->displayMessage(__FUNCTION__, $update ? 'update' : 'insert');
  }

  /**
   * Display the invoked plugin method to end user.
   *
   * @param string $method_name
   *   The invoked method name.
   * @param string $context1
   *   Additional parameter passed to the invoked method name.
   */
  protected function displayMessage($method_name, $context1 = NULL) {
    if (PHP_SAPI != 'cli') {
      $t_args = ['@class_name' => get_class($this), '@method_name' => $method_name, '@context1' => $context1];
      drupal_set_message($this->t('Invoked: @class_name:@method_name @context1', $t_args));
    }
  }

  /**
   * Form API callback. Convert password confirm array to single value.
   */
  public static function validate(array &$element, FormStateInterface $form_state) {
    drupal_set_message(t('Invoked: Drupal\yamlform_test\Plugin\YamlFormElement\YamlFormTest::validate'));
  }

}
