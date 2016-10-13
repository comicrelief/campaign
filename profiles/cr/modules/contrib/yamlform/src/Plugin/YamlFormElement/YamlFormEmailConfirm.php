<?php

namespace Drupal\yamlform\Plugin\YamlFormElement;

use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a 'email_confirm' element.
 *
 * @YamlFormElement(
 *   id = "yamlform_email_confirm",
 *   label = @Translation("Email confirm"),
 *   category = @Translation("Advanced elements"),
 *   states_wrapper = TRUE,
 * )
 */
class YamlFormEmailConfirm extends Email {

  /**
   * {@inheritdoc}
   */
  public function getDefaultProperties() {
    return parent::getDefaultProperties() + [
      'confirm__title' => '',
      'confirm__description' => '',
      'confirm__placeholder' => '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    $form['email_confirm'] = [
      '#type' => 'details',
      '#title' => $this->t('Email confirm settings'),
      '#open' => TRUE,
    ];
    $form['email_confirm']['confirm__title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Email confirm title'),
    ];
    $form['email_confirm']['confirm__description'] = [
      '#type' => 'yamlform_html_editor',
      '#title' => $this->t('Email confirm description'),
    ];
    $form['email_confirm']['confirm__placeholder'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Email confirm placeholder'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function getElementSelectorInputsOptions(array $element) {
    return [
      'mail_1' => $this->getAdminLabel($element) . '1 [' . $this->t('Email') . ']',
      'mail_2' => $this->getAdminLabel($element) . ' 2 [' . $this->t('Email') . ']',
    ];
  }

}
