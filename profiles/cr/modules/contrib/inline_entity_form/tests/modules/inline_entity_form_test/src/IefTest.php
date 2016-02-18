<?php

/**
 * @file
 * Contains \Drupal\inline_entity_form_test\IefTest
 */

namespace Drupal\inline_entity_form_test;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Tests Inline entity form element.
 */
class IefTest extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormID() {
    return 'ief_test';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['inline_entity_form'] = [
      '#type' => 'inline_entity_form',
      '#op' => 'add',
      '#entity_type' => 'node',
      '#bundle' => 'ief_test_custom',
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => t('Save'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $entity = $form_state->get(['inline_entity_form', $form['inline_entity_form']['#ief_id'], 'entity']);
    drupal_set_message(t('Created @entity_type @label.', ['@entity_type' => $entity->getEntityType()->getLabel(), '@label' => $entity->label()]));
  }

}
