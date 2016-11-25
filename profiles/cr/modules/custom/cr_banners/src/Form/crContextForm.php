<?php

/**
 * @file
 * Contains \Drupal\cr_banners\Form\crContextForm
 */
namespace Drupal\cr_banners\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure contexts used in banner/ block conditions.
 */
class crContextForm extends ConfigFormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'cr_banners_contexts';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'cr_banners.contexts',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('cr_banners.contexts');

    $form['current_context'] = array(
      '#type' => 'select',
      '#title' => $this->t('Select current site context'),
      '#default_value' => $config->get('current_context'),
      '#options' => array_map('\Drupal\Component\Utility\Html::escape', cr_banners_get_contexts()),
    );

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = \Drupal::service('config.factory')->getEditable('cr_banners.contexts');
    $config->set('current_context', $form_state->getValue('current_context'))
      ->save();

    parent::submitForm($form, $form_state);
  }
}
