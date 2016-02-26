<?php

/**
 * @file
 * Contains Drupal\search_api_page\Form\SearchApiPageDeleteForm.
 */

namespace Drupal\search_api_page\Form;

use Drupal\Core\Entity\EntityConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Builds the form to delete Search page entities.
 */
class SearchApiPageDeleteForm extends EntityConfirmFormBase {

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to delete %name?', array('%name' => $this->entity->label()));
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('entity.search_api_page.collection');
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Delete');
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->entity->delete();

    drupal_set_message($this->t('@search_page_page_label has been deleted.', ['@search_page_page_label' => $this->entity->label()]));

    // Trigger router rebuild.
    \Drupal::service('router.builder')->rebuild();

    $form_state->setRedirectUrl($this->getCancelUrl());
  }

}
