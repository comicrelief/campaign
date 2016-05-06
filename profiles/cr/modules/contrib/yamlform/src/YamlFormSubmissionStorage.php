<?php

/**
 * @file
 * Contains \Drupal\yamlform\YamlFormSubmissionStorage.
 */

namespace Drupal\yamlform;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines the YAML form submission storage.
 */
class YamlFormSubmissionStorage extends SqlContentEntityStorage implements YamlFormSubmissionStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function getFieldDefinitions() {
    /** @var \Drupal\Core\Field\BaseFieldDefinition[] $definitions */
    $field_definitions = $this->entityManager->getBaseFieldDefinitions('yamlform_submission');

    // For now never let any see or export the serialize YAML data field.
    unset($field_definitions['data']);

    $definitions = [];
    foreach ($field_definitions as $field_name => $field_definition) {
      $definitions[$field_name] = [
        'title' => $field_definition->getLabel(),
        'name' => $field_name,
        'type' => $field_definition->getType(),
        'target_type' => $field_definition->getSetting('target_type'),
      ];
    }

    return $definitions;
  }

  /**
   * {@inheritdoc}
   */
  public function deleteAll(YamlFormInterface $yamlform = NULL, $limit = NULL, $max_sid = NULL) {
    $query = $this->getQuery()
      ->sort('sid');

    if ($yamlform) {
      $query->condition('yamlform_id', $yamlform->id());
    }
    if ($limit) {
      $query->range(0, $limit);
    }
    if ($max_sid) {
      $query->condition('sid', $max_sid, '<=');
    }

    $entity_ids = $query->execute();
    $entities = $this->loadMultiple($entity_ids);
    $this->delete($entities);
    return count($entities);
  }

  /**
   * {@inheritdoc}
   */
  public function getTotal(YamlFormInterface $yamlform = NULL, AccountInterface $account = NULL) {
    $query = $this->getQuery()->count();
    if ($yamlform) {
      $query->condition('yamlform_id', $yamlform->id());
    }
    if ($account) {
      $query->condition('uid', $account->id());
    }
    return $query->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function getMaxSubmissionId(YamlFormInterface $yamlform = NULL, AccountInterface $account = NULL) {
    $query = $this->getQuery();
    $query->sort('sid', 'DESC');
    if ($yamlform) {
      $query->condition('yamlform_id', $yamlform->id());
    }
    if ($account) {
      $query->condition('uid', $account->id());
    }
    $query->range(0, 1);
    return reset($query->execute());
  }

  /**
   * {@inheritdoc}
   */
  public function getPreviousSubmission(YamlFormSubmissionInterface $yamlform_submission, AccountInterface $account) {
    return $this->getSiblingSubmission($yamlform_submission, $account, 'previous');
  }

  /**
   * {@inheritdoc}
   */
  public function getNextSubmission(YamlFormSubmissionInterface $yamlform_submission, AccountInterface $account) {
    return $this->getSiblingSubmission($yamlform_submission, $account, 'next');
  }

  /**
   * Get a YAML form submission's previous or next sibling.
   *
   * @param \Drupal\yamlform\YamlFormSubmissionInterface $yamlform_submission
   *   A YAML form submission.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The current user account.
   * @param string $direction
   *   The direction, previous or next, of the sibling to return.
   *
   * @return \Drupal\yamlform\YamlFormSubmissionInterface|null
   *   The YAML form submission's previous or next sibling.
   */
  protected function getSiblingSubmission(YamlFormSubmissionInterface $yamlform_submission, AccountInterface $account, $direction = 'previous') {
    $yamlform = $yamlform_submission->getYamlForm();

    $query = $this->getQuery();
    $query->condition('yamlform_id', $yamlform->id());
    $query->range(0, 1);

    if (!$yamlform->access('view_any', $account)) {
      $query->condition('uid', $account->id());
    }

    if ($direction == 'previous') {
      $query->condition('sid', $yamlform_submission->id(), '<');
      $query->sort('sid', 'DESC');
    }
    else {
      $query->condition('sid', $yamlform_submission->id(), '>');
      $query->sort('sid', 'ASC');
    }

    return ($entity_ids = $query->execute()) ? $this->load(reset($entity_ids)) : NULL;
  }

}
