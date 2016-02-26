<?php

/**
 * @file
 * Contains \Drupal\inline_entity_form_test\IefEditTest
 */

namespace Drupal\inline_entity_form_test;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;

/**
 * Tests Inline entity form element.
 */
class IefEditTest extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormID() {
    return 'ief_edit_test';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, Node $node = NULL) {
    $form['inline_entity_form'] = [
      '#type' => 'inline_entity_form',
      '#op' => 'edit',
      '#entity_type' => 'node',
      '#bundle' => 'ief_test_custom',
      '#entity' => $node,
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => t('Update'),
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
