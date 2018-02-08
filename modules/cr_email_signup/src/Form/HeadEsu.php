<?php

namespace Drupal\cr_email_signup\Form;

/**
 * Concrete the Header sign up.
 */
class HeadEsu extends FundraiseSignUp {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'cr_header_email_signup_form';
  }

   /**
   * {@inheritdoc}
   */
  protected function esuSubmitFields() {
    $form[$this->getFormId() . '_step1'] = [
      '#type' => 'button',
      '#name' => 'step1',
      '#value' => $this->t('Submit'),
      '#attributes' => ['class' => ['step1']],
      '#ajax' => [
        'callback' => [$this, 'processSteps'],
      ],
    ];
    return $form;
  }

}
