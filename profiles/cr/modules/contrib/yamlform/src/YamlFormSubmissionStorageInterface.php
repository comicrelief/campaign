<?php

/**
 * @file
 * Contains \Drupal\yamlform\YamlFormSubmissionStorageInterface.
 */

namespace Drupal\yamlform;

use Drupal\Core\Entity\ContentEntityStorageInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines an interface for YAML form submission classes.
 */
interface YamlFormSubmissionStorageInterface extends ContentEntityStorageInterface {

  /**
   * Get YAML form submission entity field definitions.
   *
   * The helper method is generally used for exporting results.
   *
   * @see \Drupal\yamlform\Element\YamlFormExcludedColumns
   * @see \Drupal\yamlform\Controller\YamlFormResultsExportController
   *
   * @return array
   *   An associative array of field definition key by field name containing
   *   title, name, and datatype.
   */
  public function getFieldDefinitions();

  /**
   * Delete all YAML form submissions.
   *
   * @param \Drupal\yamlform\YamlFormInterface|NULL $yamlform
   *   (optional) The YAML form to delete the submissions from.
   * @param int $limit
   *   (optional) Number of submissions to be deleted.
   * @param int $max_sid
   *   (optional) Maximum YAML form submission id.
   *
   * @return int
   *   The number of YAML form submissions deleted.
   */
  public function deleteAll(YamlFormInterface $yamlform = NULL, $limit = NULL, $max_sid = NULL);

  /**
   * Get the total number of submissions.
   *
   * @param \Drupal\yamlform\YamlFormInterface|NULL $yamlform
   *   (optional) A YAML form. If set the total number of submissions for the
   *   YAML form will be returned.
   * @param \Drupal\Core\Session\AccountInterface|NULL $account
   *   (optional) A user account.
   *
   * @return int
   *   Total number of submissions
   */
  public function getTotal(YamlFormInterface $yamlform = NULL, AccountInterface $account = NULL);

  /**
   * Get the maximum sid.
   *
   * @param \Drupal\yamlform\YamlFormInterface|NULL $yamlform
   *   (optional) A YAML form. If set the total number of submissions for the
   *   YAML form will be returned.
   * @param \Drupal\Core\Session\AccountInterface|NULL $account
   *   (optional) A user account.
   *
   * @return int
   *   Total number of submissions
   */
  public function getMaxSubmissionId(YamlFormInterface $yamlform = NULL, AccountInterface $account = NULL);

  /**
   * Get a YAML form submission's previous sibling.
   *
   * @param \Drupal\yamlform\YamlFormSubmissionInterface $yamlform_submission
   *   A YAML form submission.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The current user account.
   *
   * @return \Drupal\yamlform\YamlFormSubmissionInterface|NULL
   *   The YAML form submission's previous sibling.
   */
  public function getPreviousSubmission(YamlFormSubmissionInterface $yamlform_submission, AccountInterface $account);

  /**
   * Get a YAML form submission's next sibling.
   *
   * @param \Drupal\yamlform\YamlFormSubmissionInterface $yamlform_submission
   *   A YAML form submission.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The current user account.
   *
   * @return \Drupal\yamlform\YamlFormSubmissionInterface|NULL
   *   The YAML form submission's next sibling.
   */
  public function getNextSubmission(YamlFormSubmissionInterface $yamlform_submission, AccountInterface $account);

}
