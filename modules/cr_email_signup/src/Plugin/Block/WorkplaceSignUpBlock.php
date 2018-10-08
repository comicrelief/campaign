<?php

namespace Drupal\cr_email_signup\Plugin\Block;

use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Provides a 'Workplace Sign Up' block.
 *
 * @Block(
 *   id = "cr_email_signup_block_workplace",
 *   admin_label = @Translation("Email Sign Up block: Workplace"),
 * )
 */
class WorkplaceSignUpBlock extends BlockBase implements BlockPluginInterface {

  /**
   * Return the form.
   */
  protected function getEsuForm() {
    return \Drupal::formBuilder()->getForm('Drupal\cr_email_signup\Form\WorkplaceSignUp');
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->getConfiguration();

    $form = $this->getEsuForm();

    $messages = array_slice($config, 4);
    foreach ($messages as $keymsg => $valuemsg) {
      $classname = 'esu-' . str_replace('_', '-', $keymsg);
      $form[$keymsg] = [
        '#markup' => "<div class='$classname'><p>" . $valuemsg . "</p></div>",
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function access(AccountInterface $account, $return_as_object = FALSE) {
    return AccessResult::allowed();
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);

    $config = $this->getConfiguration();

    $form['cr_email_signup_title_message'] = [
      '#type' => 'textarea',
      '#title' => $this->t('title Message'),
      '#description' => $this->t('Enter the title message to show'),
      '#default_value' => isset($config['title_message']) ? $config['title_message'] : '',
    ];

    $form['cr_email_signup_initial_message'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Initial Message'),
      '#description' => $this->t('Enter the initial message to show'),
      '#default_value' => isset($config['initial_message']) ? $config['initial_message'] : '',
    ];

    $form['cr_email_signup_first_success_message'] = [
      '#type' => 'textarea',
      '#title' => $this->t('First Success Message'),
      '#description' => $this->t('Enter the success message'),
      '#default_value' => isset($config['first_success_message']) ? $config['first_success_message'] : '',
    ];
    $form['cr_email_signup_privacy_message'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Privacy Message'),
      '#description' => $this->t('Enter the privacy message'),
      '#default_value' => isset($config['privacy_message']) ? $config['privacy_message'] : '',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->setConfigurationValue('title_message', $form_state->getValue('cr_email_signup_title_message'));
    $this->setConfigurationValue('initial_message', $form_state->getValue('cr_email_signup_initial_message'));
    $this->setConfigurationValue('first_success_message', $form_state->getValue('cr_email_signup_first_success_message'));
    $this->setConfigurationValue('privacy_message', $form_state->getValue('cr_email_signup_privacy_message'));
  }

}
