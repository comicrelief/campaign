<?php

/**
 * @file
 * Contains \Drupal\panels_ipe\Form\PanelsIPEBlockContentForm.
 */

namespace Drupal\panels_ipe\Form;

use Drupal\block_content\BlockContentForm;
use Drupal\Core\Form\FormStateInterface;

class PanelsIPEBlockContentForm extends BlockContentForm {

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    // Override normal BlockContentForm actions as we need to be AJAX
    // compatible, and also need to communicate with our App.
    $actions['submit'] = [
      '#type' => 'button',
      '#value' => $this->t('Create and Place'),
      '#name' => 'panels_ipe_submit',
      '#ajax' => [
        'callback' => '::submitForm',
        'wrapper' => 'panels-ipe-block-type-form-wrapper',
        'method' => 'replace',
        'progress' => [
          'type' => 'throbber',
          'message' => '',
        ],
      ],
    ];

    return $actions;
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    // Wrap our form so that our submit callback can re-render the form.
    $form['#prefix'] = '<div id="panels-ipe-block-type-form-wrapper">';
    $form['#suffix'] = '</div>';

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $triggering_element = $form_state->getTriggeringElement();

    // Return early if there are any errors or if a button we're not aware of
    // submitted the form.
    if ($form_state->hasAnyErrors() || $triggering_element['#name'] !== 'panels_ipe_submit') {
      return $form;
    }

    // Submit the parent form and save. This mimics the normal behavior of the
    // submit element in our parent form(s).
    parent::submitForm($form, $form_state);
    parent::save($form, $form_state);

    // Inform the App that we've created a new Block Content entity.
    $form['#attached']['drupalSettings']['panels_ipe']['new_block_content'] = $this->entity->uuid();

    return $form;
  }

}
