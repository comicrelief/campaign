<?php

/**
 * @file
 * Contains \Drupal\yamlform\YamlFormSubmissionInterface.
 */

namespace Drupal\yamlform;

use Drupal\user\EntityOwnerInterface;
use Drupal\Core\Entity\ContentEntityInterface;

/**
 * Provides an interface defining a YAML form submission entity.
 */
interface YamlFormSubmissionInterface extends ContentEntityInterface, EntityOwnerInterface {

  /**
   * Return status for new submission.
   */
  const STATE_UNSAVED = 1;

  /**
   * Return status for submission in draft.
   */
  const STATE_DRAFT = 2;

  /**
   * Return status for submission that has been completed.
   */
  const STATE_COMPLETED = 2;

  /**
   * Return status for submission that has been updated.
   */
  const STATE_UPDATED = 3;

  /**
   * Returns the time that the submission was created.
   *
   * @return int
   *   The timestamp of when the submission was created.
   */
  public function getCreatedTime();

  /**
   * Sets the creation date of the submission.
   *
   * @param int $created
   *   The timestamp of when the submission was created.
   *
   * @return $this
   *   The class instance that this method is called on.
   */
  public function setCreatedTime($created);

  /**
   * Gets the timestamp of the last submission change.
   *
   * @return int
   *   The timestamp of the last submission save operation.
   */
  public function getChangedTime();

  /**
   * Sets the timestamp of the last submission change.
   *
   * @param int $timestamp
   *   The timestamp of the last submission save operation.
   *
   * @return $this
   */
  public function setChangedTime($timestamp);

  /**
   * Gets the timestamp of the submission completion.
   *
   * @return int
   *   The timestamp of the submission completion.
   */
  public function getCompletedTime();

  /**
   * Sets the timestamp of the submission completion.
   *
   * @param int $timestamp
   *   The timestamp of the submission completion.
   *
   * @return $this
   */
  public function setCompletedTime($timestamp);

  /**
   * Gets the remote IP addr of the submission.
   *
   * @return int
   *   The remote IP addr of the submission
   */
  public function getRemoteAddr();

  /**
   * Sets remote IP addr of the submission.
   *
   * @param int $timestamp
   *   The remote IP addr of the submission.
   *
   * @return $this
   */
  public function setRemoteAddr($timestamp);

  /**
   * Is the current submission in draft.
   *
   * @return bool
   *   TRUE if the current submission is a draft.
   */
  public function isDraft();

  /**
   * Is the current submission completed.
   *
   * @return bool
   *   TRUE if the current submission has been completed.
   */
  public function isCompleted();

  /**
   * Track the state of a submission.
   *
   * @return int
   *    Either STATE_NEW, STATE_DRAFT, STATE_COMPLETED, or STATE_UPDATED,
   *   depending on the last save operation performed.
   */
  public function getState();

  /**
   * Gets the YAML form submission's data.
   *
   * @param string $key
   *   A string that maps to a key in the submission's data.
   *   If no key is specified, then the entire data array is returned.
   *
   * @return array
   *   The YAML form submission data.
   */
  public function getData($key = NULL);

  /**
   * Set the YAML form submission's data.
   *
   * @param array $data
   *   The YAML form submission data.
   */
  public function setData(array $data);

  /**
   * Gets the YAML form submission's original data before any changes..
   *
   * @param string $key
   *   A string that maps to a key in the submission's original data.
   *   If no key is specified, then the entire data array is returned.
   *
   * @return array
   *   The YAML form submission original data.
   */
  public function getOriginalData($key = NULL);

  /**
   * Gets the YAML form submission's token.
   *
   * @return array
   *   The YAML form submission data.
   */
  public function getToken();

  /**
   * Gets the YAML form submission's YAML form entity.
   *
   * @return \Drupal\yamlform\Entity\YamlForm
   *   The YAML form entity.
   */
  public function getYamlForm();

  /**
   * Gets the YAML form submission's source entity.
   *
   * @return \Drupal\Core\Entity\EntityInterface|null
   *   The entity that this YAML form submission was created from.
   */
  public function getSourceEntity();

  /**
   * Gets the YAML form submission's source URL.
   *
   * @return \Drupal\Core\Url|false
   *   The source URL.
   */
  public function getSourceUrl();

  /**
   * Invoke all YAML form handlers method.
   *
   * @param string $method
   *   The YAML form handler method to be invoked.
   */
  public function invokeYamlFormHandlers($method);

  /**
   * Invoke a YAML form element's method.
   *
   * @param string $method
   *   The YAML form handler method to be invoked.
   * @param array $element
   *   A form element.
   */
  public function invokeYamlFormElement($method, array $element);

}
