<?php

namespace Drupal\yamlform\Plugin\YamlFormElement;

use Drupal\Core\Form\FormStateInterface;
use Drupal\yamlform\YamlFormInterface;

/**
 * Provides a 'yamlform_wizard_page' element.
 *
 * @YamlFormElement(
 *   id = "yamlform_wizard_page",
 *   label = @Translation("Wizard page"),
 *   category = @Translation("Wizard"),
 * )
 */
class YamlFormWizardPage extends Details {

  /**
   * {@inheritdoc}
   */
  public function getDefaultProperties() {
    return [
      'title' => '',
      'open' => '',
      'prev_button_label' => '',
      'next_button_label' => '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function isInput(array $element) {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function isContainer(array $element) {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function isRoot(array $element) {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    /** @var \Drupal\yamlform\YamlFormInterface $yamlform */
    $yamlform = $form_state->getFormObject()->getYamlForm();

    $form['wizard_page'] = [
      '#type' => 'details',
      '#title' => $this->t('Page settings'),
      '#open' => TRUE,
    ];
    $form['wizard_page']['prev_button_label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Previous page button label'),
      '#description' => $this->t('This is used for the Next Page button on the page before this page break.') . '<br/>' .
      $this->t('Defaults to: %value', ['%value' => $this->getDefaultSettings($yamlform, 'wizard_prev_button_label')]),
    ];
    $form['wizard_page']['next_button_label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Next page button label'),
      '#description' => $this->t('This is used for the Previous Page button on the page after this page break.') . '<br/>' .
      $this->t('Defaults to: %value', ['%value' => $this->getDefaultSettings($yamlform, 'wizard_next_button_label')]),
    ];
    return $form;
  }

  /**
   * Get default from form or global settings.
   *
   * @param \Drupal\yamlform\YamlFormInterface $yamlform
   *   A form.
   * @param string $name
   *   The name of the setting.
   *
   * @return string
   *   The setting's value.
   */
  protected function getDefaultSettings(YamlFormInterface $yamlform, $name) {
    return $yamlform->getSetting($name) ?: \Drupal::config('yamlform.settings')->get("settings.default_$name");
  }

}
