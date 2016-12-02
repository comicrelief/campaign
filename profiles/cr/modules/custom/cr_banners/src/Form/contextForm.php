<?php

/**
 * @file
 * Contains \Drupal\cr_banners\Form\contextForm
 */
namespace Drupal\cr_banners\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\cr_banners\Context\contextHandler;

/**
 * Switch between contexts used in banner/ block conditions.
 */
class contextForm extends ConfigFormBase {
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
  public function buildForm(array $form, FormStateInterface $form_state, $context_id = NULL) {
    $contexts = new contextHandler();

    $form['context_name'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Add/Edit Context'),
      '#default_value' => $contexts->getContexts($context_id),
    );

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $contexts = new contextHandler();
    $contexts->setSiteContext($form_state->getValue('current_context'));

    parent::submitForm($form, $form_state);
  }
}
