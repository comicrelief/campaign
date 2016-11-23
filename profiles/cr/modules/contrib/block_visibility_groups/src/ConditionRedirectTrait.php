<?php

namespace Drupal\block_visibility_groups;

use Drupal\Core\Form\FormStateInterface;

/**
 * Provides functionality to redirect conditions form to correct location.
 *
 * Either group edit form or block layout form.
 */
trait ConditionRedirectTrait {

  /**
   * Ensure form redirects to the correct route.
   *
   * @param \Drupal\block_visibility_groups\FormStateInterface $form_state
   */
  protected function setConditionRedirect(FormStateInterface $form_state) {
    $redirect = $form_state->getValue('bvg_redirect');
    if ($redirect == 'edit') {
      $form_state->setRedirectUrl($this->block_visibility_group->urlInfo('edit-form'));
    }
    elseif ($redirect == 'layout') {
      $query = [
        'block_visibility_group' => $this->block_visibility_group->id(),
        'show_conditions' => 1,
      ];

      $form_state->setRedirect(
        'block.admin_display',
        array(),
        ['query' => $query]

      );
    }
  }

  /**
   * Set value for redirect.
   *
   * @param $form
   * @param $redirect
   */
  protected function setRedirectValue(&$form, $redirect) {
    $form['bvg_redirect'] = [
      '#type' => 'value',
      '#value' => $redirect,
    ];
  }

}
