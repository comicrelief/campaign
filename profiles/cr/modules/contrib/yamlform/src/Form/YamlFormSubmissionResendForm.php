<?php

/**
 * @file
 * Contains \Drupal\yamlform\Form\YamlFormSubmissionResendForm.
 */

namespace Drupal\yamlform\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\yamlform\Plugin\YamlFormHandler\EmailYamlFormHandler;
use Drupal\yamlform\YamlFormSubmissionInterface;

/**
 * Defines a form that resends YAML form submission.
 */
class YamlFormSubmissionResendForm extends FormBase {

  /**
   * A YAML form submission.
   *
   * @var \Drupal\yamlform\YamlFormSubmissionInterface
   */
  protected $yamlformSubmission;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'yamlform_submission_resend';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, YamlFormSubmissionInterface $yamlform_submission = NULL) {
    $this->yamlformSubmission = $yamlform_submission;

    $handlers = $yamlform_submission->getYamlForm()->getHandlers();

    /** @var \Drupal\yamlform\YamlFormHandlerMessageInterface[] $message_handlers */
    $message_handlers = [];
    foreach ($handlers as $handler_id => $handler) {
      if ($handler instanceof EmailYamlFormHandler) {
        $message_handlers[$handler_id] = $handler;
      }
    }

    // Get header.
    $header = [];
    $header['title'] = [
      'data' => $this->t('Title / Description'),
    ];
    $header['id'] = [
      'data' => $this->t('ID'),
      'class' => [RESPONSIVE_PRIORITY_LOW],
    ];
    $header['summary'] = [
      'data' => $this->t('summary'),
      'class' => [RESPONSIVE_PRIORITY_LOW],
    ];
    $header['status'] = [
      'data' => $this->t('Status'),
      'class' => [RESPONSIVE_PRIORITY_LOW],
    ];

    // Get options.
    $options = [];
    foreach ($message_handlers as $index => $message_handler) {
      $message = $message_handler->getMessage($this->yamlformSubmission);

      $options[$index]['title'] = [
        'data' => [
          'label' => [
            '#type' => 'label',
            '#title' => $message_handler->label() . ': ' . $message_handler->description(),
            '#title_display' => NULL,
            '#for' => 'edit-message-handler-id-' . str_replace('_', '-', $message_handler->getHandlerId()),
          ],
        ],
      ];
      $options[$index]['id'] = [
        'data' => $message_handler->getHandlerId(),
      ];
      $options[$index]['summary'] = [
        'data' => $message_handler->getMessageSummary($message),
      ];
      $options[$index]['status'] = ($message_handler->isEnabled()) ? $this->t('Enabled') : $this->t('Disabled');
    }

    // Get message handler id.
    if (empty($form_state->getValue('message_handler_id'))) {
      reset($options);
      $message_handler_id = key($options);
      $form_state->setValue('message_handler_id', $message_handler_id);
    }
    else {
      $message_handler_id = $form_state->getValue('message_handler_id');
    }

    $message_handler = $this->getMessageHandler($form_state);
    $form['message_handler_id'] = [
      '#type' => 'tableselect',
      '#header' => $header,
      '#options' => $options,
      '#js_select' => TRUE,
      '#empty' => t('No messages are available.'),
      '#multiple' => FALSE,
      '#default_value' => $message_handler_id,
      '#ajax' => [
        'callback' => '::updateMessage',
        'wrapper' => 'edit-yamlform-message-wrapper',
      ],
    ];

    // Message.
    $form['message'] = [
      '#type' => 'details',
      '#title' => 'Message',
      '#open' => TRUE,
      '#tree' => TRUE,
      '#prefix' => '<div id="edit-yamlform-message-wrapper">',
      '#suffix' => '</div>',
    ];
    $message = $message_handler->getMessage($yamlform_submission);
    $form['message'] += $message_handler->resendMessageForm($message);

    // Add resend button.
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Resend message'),
    ];

    // Add submission navigation.
    $form['navigation'] = [
      '#theme' => 'yamlform_submission_navigation',
      '#yamlform_submission' => $yamlform_submission,
      '#rel' => 'email-form',
      '#weight' => -20,
    ];
    $form['#attached']['library'][] = 'yamlform/yamlform';

    return $form;
  }

  /**
   * Handles switching between messages.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   An associative array containing an email message.
   */
  public function updateMessage(array $form, FormStateInterface $form_state) {
    $message_handler = $this->getMessageHandler($form_state);
    $message = $message_handler->getMessage($this->yamlformSubmission);
    foreach ($message as $key => $value) {
      $form['message'][$key]['#value'] = $value;
    }
    return $form['message'];
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $params = $form_state->getValue('message');
    $message_handler = $this->getMessageHandler($form_state);
    $message_handler->sendMessage($params);

    $t_args = [
      '%label' => $message_handler->label(),
    ];
    drupal_set_message($this->t('Successfully re-sent %label.', $t_args));
  }

  /**
   * Get message handler from form state.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return \Drupal\yamlform\YamlFormHandlerMessageInterface
   *   The current message handler.
   */
  protected function getMessageHandler(FormStateInterface $form_state) {
    $message_handler_id = $form_state->getValue('message_handler_id');
    return $this->yamlformSubmission->getYamlForm()->getHandler($message_handler_id);
  }

}
