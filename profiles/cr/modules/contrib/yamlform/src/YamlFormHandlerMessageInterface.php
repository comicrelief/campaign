<?php

namespace Drupal\yamlform;

/**
 * Defines the interface for form handlers that send messages.
 *
 * @see \Drupal\yamlform\Plugin\YamlFormHandler\EmailYamlFormHandler
 */
interface YamlFormHandlerMessageInterface extends YamlFormHandlerInterface {

  /**
   * Get a fully populated email for a form submission.
   *
   * @param \Drupal\yamlform\YamlFormSubmissionInterface $yamlform_submission
   *   A form submission.
   *
   * @return array
   *   An array containing message parameters.
   */
  public function getMessage(YamlFormSubmissionInterface $yamlform_submission);

  /**
   * Sends and logs a form submission message.
   *
   * @param array $message
   *   An array of message parameters.
   */
  public function sendMessage(array $message);

  /**
   * Build resend message form.
   *
   * @param array $message
   *   An array of message parameters.
   *
   * @return array
   *   A form to edit a message.
   */
  public function resendMessageForm(array $message);

  /**
   * Build message summary.
   *
   * @param array $message
   *   An array of message parameters.
   *
   * @return array
   *   A renderable array representing a message summary.
   */
  public function getMessageSummary(array $message);

}
