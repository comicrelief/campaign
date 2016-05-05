<?php

/**
 * @file
 * Contains \Drupal\yamlform\YamlFormHandlerInterface.
 */

namespace Drupal\yamlform;

use Drupal\Component\Plugin\ConfigurablePluginInterface;
use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\PluginFormInterface;

/**
 * Defines the interface for YAML form handlers.
 *
 * @see \Drupal\yamlform\Annotation\YamlFormHandler
 * @see \Drupal\yamlform\YamlFormHandlerBase
 * @see \Drupal\yamlform\YamlFormHandlerManager
 * @see plugin_api
 */
interface YamlFormHandlerInterface extends PluginInspectionInterface, ConfigurablePluginInterface, ContainerFactoryPluginInterface, PluginFormInterface {

  /**
   * Value indicating unlimited plugin instances are permitted.
   */
  const CARDINALITY_UNLIMITED = -1;

  /**
   * Value indicating a single plugin instances are permitted.
   */
  const CARDINALITY_SINGLE = 1;

  /**
   * Value indicating YAMl form submissions are not processed (ie email or saved) by the handler.
   */
  const RESULTS_IGNORED = 0;

  /**
   * Value indicating YAMl form submissions are processed (ie email or saved) by the handler.
   */
  const RESULTS_PROCESSED = 1;

  /**
   * Returns a render array summarizing the configuration of the YAML form handler.
   *
   * @return array
   *   A render array.
   */
  public function getSummary();

  /**
   * Returns the YAML form handler label.
   *
   * @return string
   *   The YAML form handler label.
   */
  public function label();

  /**
   * Returns the YAML form handler description.
   *
   * @return string
   *   The YAML form handler description.
   */
  public function description();

  /**
   * Returns the YAML form handler cardinality settings.
   *
   * @return string
   *   The YAML form handler cardinality settings.
   */
  public function cardinality();

  /**
   * Returns the unique ID representing the YAML form handler.
   *
   * @return string
   *   The YAML form handler ID.
   */
  public function getHandlerId();

  /**
   * Sets the id for this YAML form handler.
   *
   * @param int $handler_id
   *   The handler_id for this YAML form handler.
   *
   * @return $this
   */
  public function setHandlerId($handler_id);

  /**
   * Returns the label of the YAML form handler.
   *
   * @return int|string
   *   Either the integer label of the YAML form handler, or an empty string.
   */
  public function getLabel();

  /**
   * Sets the label for this YAML form handler.
   *
   * @param int $label
   *   The label for this YAML form handler.
   *
   * @return $this
   */
  public function setLabel($label);

  /**
   * Returns the weight of the YAML form handler.
   *
   * @return int|string
   *   Either the integer weight of the YAML form handler, or an empty string.
   */
  public function getWeight();

  /**
   * Sets the weight for this YAML form handler.
   *
   * @param int $weight
   *   The weight for this YAML form handler.
   *
   * @return $this
   */
  public function setWeight($weight);

  /**
   * Returns the status of the YAML form handler.
   *
   * @return bool
   *   The status of the YAML form handler.
   */
  public function getStatus();

  /**
   * Sets the status for this YAML form handler.
   *
   * @param bool $status
   *   The status for this YAML form handler.
   *
   * @return $this
   */
  public function setStatus($status);

  /**
   * Returns the YAML form handler enabled indicator.
   *
   * @return bool
   *   TRUE if the YAML form handler is enabled.
   */
  public function isEnabled();

  /**
   * Returns the YAML form handler disabled indicator.
   *
   * @return bool
   *   TRUE if the YAML form handler is disabled.
   */
  public function isDisabled();

  /**
   * Alter YAML form submission form inputs.
   *
   * @param array $inputs
   *   An associative array containing the YAML form inputs.
   * @param \Drupal\yamlform\YamlFormInterface $yamlform
   *   The YAML form.
   */
  public function alterInputs(array &$inputs, YamlFormInterface $yamlform);

  /**
   * Alter YAML form submission form .
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param \Drupal\yamlform\YamlFormSubmissionInterface $yamlform_submission
   *   A YAML form submission.
   */
  public function alterForm(array &$form, FormStateInterface $form_state, YamlFormSubmissionInterface $yamlform_submission);

  /**
   * Validate YAML form submission form .
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param \Drupal\yamlform\YamlFormSubmissionInterface $yamlform_submission
   *   A YAML form submission.
   */
  public function validateForm(array &$form, FormStateInterface $form_state, YamlFormSubmissionInterface $yamlform_submission);

  /**
   * Submit YAML form submission form.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param \Drupal\yamlform\YamlFormSubmissionInterface $yamlform_submission
   *   A YAML form submission.
   */
  public function submitForm(array &$form, FormStateInterface $form_state, YamlFormSubmissionInterface $yamlform_submission);

  /**
   * Changes the values of an entity before it is created.
   *
   * @param mixed[] $values
   *   An array of values to set, keyed by property name.
   */
  public function preCreate(array $values);

  /**
   * Acts on a YAML form submission after it is created.
   *
   * @param \Drupal\yamlform\YamlFormSubmissionInterface $yamlform_submission
   *   A YAML form submission.
   */
  public function postCreate(YamlFormSubmissionInterface $yamlform_submission);

  /**
   * Acts on loaded YAML form submission.
   *
   * @param \Drupal\yamlform\YamlFormSubmissionInterface $yamlform_submission
   *   A YAML form submission.
   */
  public function postLoad(YamlFormSubmissionInterface $yamlform_submission);

  /**
   * Acts on a YAML form submission before the presave hook is invoked.
   *
   * @param \Drupal\yamlform\YamlFormSubmissionInterface $yamlform_submission
   *   A YAML form submission.
   */
  public function preSave(YamlFormSubmissionInterface $yamlform_submission);

  /**
   * Acts on a saved YAML form submission before the insert or update hook is invoked.
   *
   * @param \Drupal\yamlform\YamlFormSubmissionInterface $yamlform_submission
   *   A YAML form submission.
   * @param bool $update
   *   TRUE if the entity has been updated, or FALSE if it has been inserted.
   */
  public function postSave(YamlFormSubmissionInterface $yamlform_submission, $update = TRUE);

  /**
   * Acts on a YAML form submission before they are deleted and before hooks are invoked.
   *
   * Used before the entities are deleted and before invoking the delete hook.
   *
   * @param \Drupal\yamlform\YamlFormSubmissionInterface $yamlform_submission
   *   A YAML form submission.
   */
  public function preDelete(YamlFormSubmissionInterface $yamlform_submission);

  /**
   * Acts on deleted a YAML form submission before the delete hook is invoked.
   *
   * Used after the entities are deleted but before invoking the delete hook.
   *
   * @param \Drupal\yamlform\YamlFormSubmissionInterface $yamlform_submission
   *   A YAML form submission.
   */
  public function postDelete(YamlFormSubmissionInterface $yamlform_submission);

}
