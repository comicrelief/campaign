<?php

/**
 * @file
 * Contains \Drupal\config_readonly\ReadOnlyFormEvent.
 */

namespace Drupal\config_readonly;

use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Readonly form event.
 */
class ReadOnlyFormEvent extends Event {

  const NAME = 'config_readonly_form_event';

  protected $formState;

  protected $readOnlyForm;

  public function __construct(FormStateInterface $form_state) {
    $this->readOnlyForm = false;
    $this->formState = $form_state;
  }

  public function getFormState() {
    return $this->formState;
  }

  public function markFormReadOnly() {
    $this->readOnlyForm = true;
  }

  public function isFormReadOnly() {
    return $this->readOnlyForm;
  }
}

