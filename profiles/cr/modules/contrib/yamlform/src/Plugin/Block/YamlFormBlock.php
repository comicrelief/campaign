<?php

namespace Drupal\yamlform\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\yamlform\Entity\YamlForm;

/**
 * Provides a 'Form' block.
 *
 * @Block(
 *   id = "yamlform_block",
 *   admin_label = @Translation("Form"),
 *   category = @Translation("Form")
 * )
 */
class YamlFormBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'yamlform_id' => '',
      'default_data' => '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form['yamlform_id'] = [
      '#title' => $this->t('Form'),
      '#type' => 'entity_autocomplete',
      '#target_type' => 'yamlform',
      '#required' => TRUE,
      '#default_value' => $this->getYamlForm(),
    ];
    $form['default_data'] = [
      '#title' => $this->t('Default form submission data (YAML)'),
      '#description' => $this->t('Enter form submission data as name and value pairs which will be used to prepopulate the selected form. You may use tokens.'),
      '#type' => 'yamlform_codemirror',
      '#mode' => 'yaml',
      '#default_value' => $this->configuration['default_data'],
    ];
    $form['token_tree_link'] = [
      '#theme' => 'token_tree_link',
      '#token_types' => [
        'yamlform',
        'yamlform-submission',
      ],
      '#click_insert' => FALSE,
      '#dialog' => TRUE,
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['yamlform_id'] = $form_state->getValue('yamlform_id');
    $this->configuration['default_data'] = $form_state->getValue('default_data');
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $values = ['data' => $this->configuration['default_data']];
    return $this->getYamlForm()->getSubmissionForm($values);
  }

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    $yamlform = $this->getYamlForm();
    if (!$yamlform || !$yamlform->access('submission_create', $account)) {
      return AccessResult::forbidden();
    }
    else {
      return parent::blockAccess($account);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    // Caching strategy is handled by the form.
    return 0;
  }

  /**
   * Get this block instance form.
   *
   * @return \Drupal\yamlform\Entity\YamlForm
   *   A form or NULL.
   */
  protected function getYamlForm() {
    return YamlForm::load($this->configuration['yamlform_id']);
  }

}
