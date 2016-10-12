<?php

namespace Drupal\yamlform\Plugin\YamlFormElement;

use Drupal\Core\Form\FormStateInterface;
use Drupal\yamlform\YamlFormElementBase;
use Drupal\yamlform\YamlFormSubmissionInterface;

/**
 * Provides a 'captcha' element.
 *
 * @YamlFormElement(
 *   id = "captcha",
 *   api = "https://www.drupal.org/project/captcha",
 *   label = @Translation("CAPTCHA"),
 *   category = @Translation("Advanced elements"),
 *   states_wrapper = TRUE,
 * )
 */
class Captcha extends YamlFormElementBase {

  /**
   * {@inheritdoc}
   */
  public function getDefaultProperties() {
    return [
      'captcha_type' => 'default',
      'captcha_admin_mode' => FALSE,
      'flex' => 1,
      'states' => [],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function isInput(array $element) {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultFormat() {
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormats() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function prepare(array &$element, YamlFormSubmissionInterface $yamlform_submission) {
    // Enable admin mode for test or user with 'skip CAPTCHA' permission.
    $is_test = (strpos(\Drupal::routeMatch()->getRouteName(), '.yamlform.test') !== FALSE) ? TRUE : FALSE;
    $is_admin = \Drupal::currentUser()->hasPermission('skip CAPTCHA');
    if ($is_test || $is_admin) {
      $element['#captcha_admin_mode'] = TRUE;
    }
    parent::prepare($element, $yamlform_submission);
  }

  /**
   * {@inheritdoc}
   */
  public function preSave(array &$element, YamlFormSubmissionInterface $yamlform_submission) {
    // Remove all captcha related keys from the form submission's data.
    $key = $element['#yamlform_key'];
    $data = $yamlform_submission->getData();
    unset($data[$key]);
    // @see \Drupal\captcha\Element\Captcha
    $sub_keys = ['sid', 'token', 'response'];
    foreach ($sub_keys as $sub_key) {
      unset($data[$key . '_' . $sub_key]);
    }
    $yamlform_submission->setData($data);
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    if (\Drupal::moduleHandler()->moduleExists('captcha')) {
      module_load_include('inc', 'captcha', 'captcha.admin');
      $captcha_types = _captcha_available_challenge_types();
    }
    else {
      $captcha_types = ['default' => $this->t('Default challenge type')];
    }
    $form['captcha'] = [
      '#type' => 'details',
      '#title' => $this->t('CAPTCHA settings'),
      '#open' => FALSE,
    ];
    $form['captcha']['captcha_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Challenge type'),
      '#required' => TRUE,
      '#options' => $captcha_types,
    ];
    $form['captcha']['captcha_admin_mode'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Admin mode'),
      '#description' => $this->t('Presolve the CAPTCHA and always shows it. This is useful for debugging and preview CAPTCHA integration.'),
      '#return_value' => TRUE,
    ];
    return $form;
  }

}
