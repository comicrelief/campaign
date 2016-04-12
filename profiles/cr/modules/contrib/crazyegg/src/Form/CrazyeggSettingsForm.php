<?php

/**
 * @file
 * Contains Drupal\crazyegg\Form\CrazyeggSettingsForm.
 */

namespace Drupal\crazyegg\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Render\Element;

/**
 * Returns responses for Crazyegg module routes.
 */
class CrazyeggSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'crazyegg_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('crazyegg.settings');

    foreach (Element::children($form) as $variable) {
      $config->set($variable, $form_state->getValue($form[$variable]['#parents']));
    }
    $config->save();

    if (method_exists($this, '_submitForm')) {
      $this->_submitForm($form, $form_state);
    }

    parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['crazyegg.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, \Drupal\Core\Form\FormStateInterface $form_state) {
    $form = [];

    $form['crazyegg_enabled'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Enable Crazy Egg?'),
      '#default_value' => \Drupal::config('crazyegg.settings')->get('crazyegg_enabled'),
    );

    $description = $this->t('To find your ID, log in to your <a href="@link">CrazyEgg account</a> and click the "What\'s my code" link at the top of your Dashboard. (ex. 00111111)', array('@link' => 'http://www.crazyegg.com'));
    $form['crazyegg_account_id'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Crazy Egg Account ID'),
      '#default_value' => \Drupal::config('crazyegg.settings')->get('crazyegg_account_id'),
      '#description' => $description,
    );

    return parent::buildForm($form, $form_state);
  }

}
