<?php

namespace Drupal\cr_email_signup\Form;

/**
 * Concrete implementation of Step One.
 */
class StandardSignUp extends SignUp {

  /**
   * Get the Form Identifier.
   */
  public function getFormId() {
    return 'cr_email_signup_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function esuContentFields() {
    $form['school_phase'] = [
      '#type' => 'select',
      '#title' => $this->t('Also send me School resources'),
      '#empty_option' => $this->t('-- Select age group --'),
      '#options' => [
        'EY' => 'Early Years or Nursery',
        'PY' => 'Primary',
        'SY' => 'Secondary',
        'FE' => 'Further Education or Sixth-Form College',
        'HE' => 'Higher Education',
        'OH' => 'Other',
      ],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function esuSubmitFields() {
    $form = parent::esuSubmitFields();
    $form[$this->getFormId() . '_step2'] = [
      '#type' => 'button',
      '#name' => 'step2',
      '#value' => $this->t('Go'),
      '#attributes' => ['class' => ['step2']],
      '#ajax' => [
        'callback' => [$this, 'processSteps'],
      ],
    ];
    return $form;
  }

}
