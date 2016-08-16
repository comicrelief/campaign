<?php

namespace Drupal\cr_email_signup\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\InvokeCommand;

/**
 * Concrete implementation of Step One.
 */
class WorkplaceSignUp extends SignUp {

  /**
   * Get the Form Identifier.
   */
  public function getFormId() {
    return 'cr_email_signup_workplace_form';
  }

  /**
   * {@inheritdoc}
   */
  public function getQueueName() {
    return 'esu_workplace';
  }

  /**
   * Send a message to the queue service.
   *
   * @param array $append_message
   *     Message to append to queue.
   */
  protected function queueMessage($append_message) {
    // Add dynamic keys.
    $append_message['timestamp'] = time();
    $append_message['transSourceURL'] = \Drupal::service('path.current')->getPath();
    $append_message['transSource'] = "{$this->skeletonMessage['campaign']}_[Device]_ESU_[PageElementSource]";

    // RND-178: Device & Source Replacements.
    if (!empty($append_message['device'])) {
      $append_message['transSource'] = str_replace("[Device]", $append_message['device'], $append_message['transSource']);
    }
    else {
      $append_message['transSource'] = str_replace("[Device]", "Unknown", $append_message['transSource']);
    }
    if (!empty($append_message['source'])) {
      $append_message['transSource'] = str_replace("[PageElementSource]", $append_message['source'], $append_message['transSource']);
    }
    else {
      $append_message['transSource'] = str_replace("[PageElementSource]", "Unknown", $append_message['transSource']);
    }

    // Add passed arguments.
    $queue_message = array_merge($this->skeletonMessage, $append_message);

    try {
      $queue = $queue_factory->get($this->getQueueName());

      if (FALSE === $queue->createItem($queue_message)) {
        throw new \Exception("createItem Failed. Check Queue.");
      }
    }
    catch (\Exception $exception) {
      \Drupal::logger('cr_email_signup_workplace')->error("Unable to queue message. Attempted to queue message. Error was: " . $exception->getMessage());
    }
  }

  /**
   * Build the Form Elements.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form_state = $form_state;
    $form['steps']['email'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Your email address'),
      '#placeholder' => $this->t('Enter your email address'),
    ];
    $form['steps']['device'] = [
      '#name' => 'device',
      '#type' => 'hidden',
      '#attributes' => array(
        'id' => 'esu-device',
      ),
    ];
    $form['steps']['source'] = [
      '#name' => 'source',
      '#type' => 'hidden',
      '#attributes' => array(
        'id' => 'esu-source',
      ),
    ];

    $form['steps']['step1'] = [
      '#type' => 'button',
      '#name' => 'step1',
      '#value' => $this->t('Go'),
      '#attributes' => ['class' => ['step1']],
      '#ajax' => [
        'callback' => [$this, 'processSteps'],
      ],
    ];

    return $form;
  }

  /**
   * Process form steps.
   */
  public function processSteps(array &$form, FormStateInterface $form_state) {
    $triggering_element = $form_state->getTriggeringElement();
    $response = new AjaxResponse();
    switch ($triggering_element['#name']) {
      case 'step1':
        // Process first step.
        if ($this->validateEmail($form, $form_state)) {
          // Send first message to queue.
          $this->queueMessage(array(
            'email' => $form_state->getValue('email'),
            'device' => $form_state->getValue('device'),
            'source' => $form_state->getValue('source'),
            'lists' => array('general' => 'general'),
          ));
          $response->addCommand(new HtmlCommand('.esu-errors', ''));
          $response->addCommand(new InvokeCommand('.block--cr-email-signup', 'removeClass', array('block--cr-email-signup--error')));
          $response->addCommand(new InvokeCommand('.block--cr-email-signup', 'removeClass', array('block--cr-email-signup--step-1')));
          $response->addCommand(new InvokeCommand('.block--cr-email-signup', 'addClass', array('block--cr-email-signup--step-2')));
        }
        else {
          // Error if validation isnt met.
          $response->addCommand(new HtmlCommand('.esu-errors', 'Please enter a valid email address'));
          $response->addCommand(new InvokeCommand('.block--cr-email-signup', 'addClass', array('block--cr-email-signup--error')));
        }
        break;
    }
    // Return ajax response.
    return $response;
  }

}
