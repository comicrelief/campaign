<?php

/**
 * @file
 * Contains Drupal\paragraphs\Form\ParagraphsTypeForm.
 */

namespace Drupal\paragraphs\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

class ParagraphsTypeForm extends EntityForm
{

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $paragraphs_type = $this->entity;
    $form['label'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $paragraphs_type->label(),
      '#description' => $this->t("Label for the Paragraphs type."),
      '#required' => TRUE,
    );

    $form['id'] = array(
      '#type' => 'machine_name',
      '#default_value' => $paragraphs_type->id(),
      '#machine_name' => array(
        'exists' => 'paragraphs_type_load',
      ),
      '#disabled' => !$paragraphs_type->isNew(),
    );

    // You will need additional form elements for your custom properties.

    return $form;
  }

  /**
  * {@inheritdoc}
  */
  public function save(array $form, FormStateInterface $form_state) {
    $paragraphs_type = $this->entity;
    $status = $paragraphs_type->save();

    if ($status) {
      drupal_set_message($this->t('Saved the %label Paragraphs type.', array(
        '%label' => $paragraphs_type->label(),
      )));
    }
    else {
      drupal_set_message($this->t('The %label Paragraphs type was not saved.', array(
        '%label' => $paragraphs_type->label(),
      )));
    }
    $form_state->setRedirect('entity.paragraphs_type.collection');
  }
}
