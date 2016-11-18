<?php

namespace Drupal\context\Reaction;

use Drupal\context\ContextInterface;
use Drupal\context\ContextReactionInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

abstract class ContextReactionFormBase extends FormBase {

  /**
   * @var ContextInterface
   */
  protected $context;

  /**
   * @var ContextReactionInterface
   */
  protected $reaction;

  /**
   * Form constructor.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @param \Drupal\context\ContextInterface $context
   *   The context that contains the reaction.
   *
   * @param $reaction_id
   *   The id of the reaction that is being configured.
   *
   * @return array The form structure.
   * The form structure.
   */
  public function buildForm(array $form, FormStateInterface $form_state, ContextInterface $context = NULL, $reaction_id = NULL) {
    $this->context = $context;
    $this->reaction = $this->context->getReaction($reaction_id);

    $form['reaction'] = [
      '#tree' => TRUE,
    ];

    $form['actions'] = [
      '#type' => 'actions'
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->context->save();
  }
}
