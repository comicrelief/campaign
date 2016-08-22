<?php

namespace Drupal\cr_email_signup\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\InvokeCommand;

/**
 * Concrete implementation of Step One.
 */
abstract class SignUp extends FormBase {

  /**
   * Array to send to queue. Some key values should be sourced from config.
   *
   * @var array
   *     Skeleton message to send
   */
  protected $skeletonMessage = [
    // TODO: Should this be hardcoded??
    'campaign' => 'RND17',
    'transType' => 'esu',
    'timestamp' => NULL,
    'transSourceURL' => NULL,
    'transSource' => NULL,
    'email' => NULL,
  ];

  /**
   * Returns the queue name.
   *
   * @return string
   *   The string identifying the queue.
   */
  abstract protected function getQueueName();

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
      $append_message['transSource'] = str_replace(
        "[Device]",
        $append_message['device'],
        $append_message['transSource']
      );
    }
    else {
      $append_message['transSource'] = str_replace(
        "[Device]",
        "Unknown",
        $append_message['transSource']
      );
    }
    if (!empty($append_message['source'])) {
      $append_message['transSource'] = str_replace(
        "[PageElementSource]",
        $append_message['source'],
        $append_message['transSource']
      );
    }
    else {
      $append_message['transSource'] = str_replace(
        "[PageElementSource]",
        "Unknown",
        $append_message['transSource']
      );
    }

    // Add passed arguments.
    $queue_message = array_merge($this->skeletonMessage, $append_message);
    try {
      $queue_factory = \Drupal::service('queue');
      $queue = $queue_factory->get($this->getQueueName());

      if (FALSE === $queue->createItem($queue_message)) {
        throw new \Exception("createItem Failed. Check Queue.");
      }
    }
    catch (\Exception $exception) {
      \Drupal::logger('cr_email_signup')->error(
        "Unable to queue message. Attempted to queue message. Error was: " . $exception->getMessage()
      );
    }
  }

  /**
   * Build the Form Elements.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form += $this->esuRequiredFields();
    $form += $this->esuContentFields();
    $form += $this->esuSubmitFields();

    return $form;
  }

  /**
   * Build the mandatory fields of the form.
   */
  protected function esuRequiredFields() {
    $form['device'] = [
      '#name' => 'device',
      '#type' => 'hidden',
      '#attributes' => [
        'class' => 'esu-device',
      ],
    ];
    $form['source'] = [
      '#name' => 'source',
      '#type' => 'hidden',
      '#attributes' => [
        'class' => 'esu-source',
      ],
    ];
    $form['email'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Your email address'),
      '#placeholder' => $this->t('Enter your email address'),
    ];
    return $form;
  }

  /**
   * Build the extra elements of the form.
   */
  protected function esuContentFields() {
    return [];
  }

  /**
   * Build the submit elements.
   */
  protected function esuSubmitFields() {
    $form['step1'] = [
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
   * Custom email validate function.
   */
  public function validateEmail(array &$form, FormStateInterface $form_state) {
    $email_address = $form_state->getValue('email');

    return (filter_var($email_address, FILTER_VALIDATE_EMAIL) && strlen($email_address) <= 100) ? TRUE : FALSE;
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
          // @TODO: Refactor this!
          $data = [
            'email' => $form_state->getValue('email'),
            'device' => $form_state->getValue('device'),
            'source' => $form_state->getValue('source'),
            'lists' => ['general' => 'general'],
          ];
          if ($form_state->getValue('firstName')) {
            $data['firstName'] = $form_state->getValue('firstName');
          }
          if ($form_state->getValue('EventInterest')) {
            $data['EventInterest'] = $form_state->getValue('EventInterest');
          }
          $this->queueMessage($data);
          $response->addCommand(new HtmlCommand('.esu-errors', ''));
          $response->addCommand(new InvokeCommand(
            '.block--cr-email-signup',
            'removeClass',
           ['block--cr-email-signup--error']
          ));
          $response->addCommand(new InvokeCommand(
            '.block--cr-email-signup',
            'removeClass',
            ['block--cr-email-signup--step-1']
          ));
          $response->addCommand(new InvokeCommand(
            '.block--cr-email-signup',
            'addClass',
            ['block--cr-email-signup--step-2']
          ));
        }
        else {
          // Error if validation isnt met.
          $response->addCommand(new HtmlCommand(
            '.esu-errors', 'Please enter a valid email address'
          ));
          $response->addCommand(new InvokeCommand(
            '.block--cr-email-signup',
            'addClass',
            ['block--cr-email-signup--error']
          ));
        }
        break;

      case 'step2':
        // Process second step.
        if (!$form_state->isValueEmpty('school_phase') && $this->validateEmail($form, $form_state)) {
          // Send second message to the queue.
          $this->queueMessage([
            'email' => $form_state->getValue('email'),
            'phase' => $form_state->getValue('school_phase'),
            'device' => $form_state->getValue('device'),
            'source' => $form_state->getValue('source'),
            'lists' => ['teacher' => 'teacher'],
          ]);
          $response->addCommand(new InvokeCommand(
            '.block--cr-email-signup',
            'removeClass',
            ['block--cr-email-signup--error']
          ));
          $response->addCommand(new InvokeCommand(
            '.block--cr-email-signup',
            'removeClass',
            ['block--cr-email-signup--step-2']
          ));
          $response->addCommand(new InvokeCommand(
            '.block--cr-email-signup',
            'addClass',
            ['block--cr-email-signup--step-3']
          ));

        }
        else {
          // Error if age range isnt selected.
          $response->addCommand(new HtmlCommand(
            '.esu-errors',
            'Please select an age group.'
          ));
          $response->addCommand(new InvokeCommand(
            '.block--cr-email-signup',
            'addClass',
            ['block--cr-email-signup--error']
          ));
          return $response;

        }
        break;
    }
    // Return ajax response.
    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    return $form;
  }

}
