<?php

namespace Drupal\yamlform;

use Drupal\Component\Serialization\Yaml;
use Drupal\Core\Database\Database;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
use Drupal\Core\Session\AccountInterface;
use Drupal\yamlform\Entity\YamlForm;

/**
 * Defines the form submission storage.
 */
class YamlFormSubmissionStorage extends SqlContentEntityStorage implements YamlFormSubmissionStorageInterface {

  /**
   * Array used to element data schema.
   *
   * @var array
   */
  protected $elementDataSchema = [];

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
  public function loadDraft(YamlFormInterface $yamlform, EntityInterface $source_entity = NULL, AccountInterface $account = NULL) {
    $query = $this->getQuery();
    $query->condition('in_draft', 1);
    $query->condition('yamlform_id', $yamlform->id());
    $query->condition('uid', $account->id());
    if ($source_entity) {
      $query->condition('entity_type', $source_entity->getEntityTypeId());
      $query->condition('entity_id', $source_entity->id());
    }
    else {
      $query->notExists('entity_type');
      $query->notExists('entity_id');
    }
    if ($entity_ids = $query->execute()) {
      return $this->load(reset($entity_ids));
    }
    else {
      return NULL;
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function doCreate(array $values) {
    /** @var \Drupal\yamlform\YamlFormSubmissionInterface $entity */
    $entity = parent::doCreate($values);
    if (!empty($values['data'])) {
      $data = (is_array($values['data'])) ? $values['data'] : Yaml::decode($values['data']);
      $entity->setData($data);
    }
    return $entity;
  }

  /**
   * {@inheritdoc}
   */
  public function loadMultiple(array $ids = NULL) {
    /** @var \Drupal\yamlform\YamlFormSubmissionInterface[] $yamlform_submissions */
    $yamlform_submissions = parent::loadMultiple($ids);
    $this->loadData($yamlform_submissions);
    return $yamlform_submissions;
  }

  /**
   * {@inheritdoc}
   */
  public function deleteAll(YamlFormInterface $yamlform = NULL, EntityInterface $source_entity = NULL, $limit = NULL, $max_sid = NULL) {
    $query = $this->getQuery()
      ->sort('sid');
    if ($yamlform) {
      $query->condition('yamlform_id', $yamlform->id());
    }
    if ($source_entity) {
      $query->condition('entity_type', $source_entity->getEntityTypeId());
      $query->condition('entity_id', $source_entity->id());
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
  public function getTotal(YamlFormInterface $yamlform = NULL, EntityInterface $source_entity = NULL, AccountInterface $account = NULL) {
    $query = $this->getQuery()->count();
    if ($yamlform) {
      $query->condition('yamlform_id', $yamlform->id());
    }
    if ($source_entity) {
      $query->condition('entity_type', $source_entity->getEntityTypeId());
      $query->condition('entity_id', $source_entity->id());
    }
    if ($account) {
      $query->condition('uid', $account->id());
    }
    return $query->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function getMaxSubmissionId(YamlFormInterface $yamlform = NULL, EntityInterface $source_entity = NULL, AccountInterface $account = NULL) {
    $query = $this->getQuery();
    $query->sort('sid', 'DESC');
    if ($yamlform) {
      $query->condition('yamlform_id', $yamlform->id());
    }
    if ($source_entity) {
      $query->condition('entity_type', $source_entity->getEntityTypeId());
      $query->condition('entity_id', $source_entity->id());
    }
    if ($account) {
      $query->condition('uid', $account->id());
    }
    $query->range(0, 1);
    $result = $query->execute();
    return reset($result);
  }

  /****************************************************************************/
  // Paging methods.
  /****************************************************************************/

  /**
   * {@inheritdoc}
   */
  public function getFirstSubmission(YamlFormInterface $yamlform, EntityInterface $source_entity = NULL, AccountInterface $account = NULL) {
    return $this->getTerminusSubmission($yamlform, $source_entity, $account, 'ASC');
  }

  /**
   * {@inheritdoc}
   */
  public function getLastSubmission(YamlFormInterface $yamlform, EntityInterface $source_entity = NULL, AccountInterface $account = NULL) {
    return $this->getTerminusSubmission($yamlform, $source_entity, $account, 'DESC');
  }

  /**
   * {@inheritdoc}
   */
  public function getPreviousSubmission(YamlFormSubmissionInterface $yamlform_submission, EntityInterface $source_entity = NULL, AccountInterface $account = NULL) {
    return $this->getSiblingSubmission($yamlform_submission, $source_entity, $account, 'previous');
  }

  /**
   * {@inheritdoc}
   */
  public function getNextSubmission(YamlFormSubmissionInterface $yamlform_submission, EntityInterface $source_entity = NULL, AccountInterface $account = NULL) {
    return $this->getSiblingSubmission($yamlform_submission, $source_entity, $account, 'next');
  }

  /**
   * {@inheritdoc}
   */
  public function getSourceEntityTypes(YamlFormInterface $yamlform) {
    $entity_types = Database::getConnection()->select('yamlform_submission', 's')
      ->distinct()
      ->fields('s', ['entity_type'])
      ->condition('s.yamlform_id', $yamlform->id())
      ->condition('s.entity_type', 'yamlform', '<>')
      ->orderBy('s.entity_type', 'ASC')
      ->execute()
      ->fetchCol();

    $entity_type_labels = \Drupal::entityManager()->getEntityTypeLabels();
    ksort($entity_type_labels);

    return array_intersect_key($entity_type_labels, array_flip($entity_types));
  }

  /**
   * {@inheritdoc}
   */
  protected function getTerminusSubmission(YamlFormInterface $yamlform, EntityInterface $source_entity = NULL, AccountInterface $account = NULL, $sort = 'DESC') {
    $query = $this->getQuery();
    $query->condition('yamlform_id', $yamlform->id());
    $query->range(0, 1);
    if ($source_entity) {
      $query->condition('entity_type', $source_entity->getEntityTypeId());
      $query->condition('entity_id', $source_entity->id());
    }
    if ($account) {
      $query->condition('uid', $account->id());
    }
    $query->sort('sid', $sort);
    return ($entity_ids = $query->execute()) ? $this->load(reset($entity_ids)) : NULL;
  }

  /**
   * {@inheritdoc}
   */
  protected function getSiblingSubmission(YamlFormSubmissionInterface $yamlform_submission, EntityInterface $entity = NULL, AccountInterface $account = NULL, $direction = 'previous') {
    $yamlform = $yamlform_submission->getYamlForm();

    $query = $this->getQuery();
    $query->condition('yamlform_id', $yamlform->id());
    $query->range(0, 1);

    if ($entity) {
      $query->condition('entity_type', $entity->getEntityTypeId());
      $query->condition('entity_id', $entity->id());
    }

    if ($account) {
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

  /****************************************************************************/
  // YamlFormSubmissionEntityList methods.
  /****************************************************************************/

  /**
   * {@inheritdoc}
   */
  public function getCustomColumns(YamlFormInterface $yamlform = NULL, EntityInterface $source_entity = NULL, AccountInterface $account = NULL, $include_elements = TRUE) {
    // Get custom columns from the form's state.
    if ($source_entity) {
      $source_key = $source_entity->getEntityTypeId() . '.' . $source_entity->id();
      $custom_column_names = $yamlform->getState("results.custom.columns.$source_key", []);
      // If the source entity does not have custom columns, then see if we
      // can use the main form as the default custom columns.
      if (empty($custom_column_names) && $yamlform->getState("results.custom.default", FALSE)) {
        $custom_column_names = $yamlform->getState('results.custom.columns', []);
      }
    }
    else {
      $custom_column_names = $yamlform->getState('results.custom.columns', []);
    }

    if (empty($custom_column_names)) {
      return $this->getDefaultColumns($yamlform, $source_entity, $account, $include_elements);
    }

    // Get custom column with labels.
    $columns = $this->getColumns($yamlform, $source_entity, $account, $include_elements);
    $custom_columns = [];
    foreach ($custom_column_names as $column_name) {
      if (isset($columns[$column_name])) {
        $custom_columns[$column_name] = $columns[$column_name];
      }
    }
    return $custom_columns;
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultColumns(YamlFormInterface $yamlform = NULL, EntityInterface $source_entity = NULL, AccountInterface $account = NULL, $include_elements = TRUE) {
    $columns = $this->getColumns($yamlform, $source_entity, $account, $include_elements);

    // Hide certain unnecessary columns, that have default set to FALSE.
    foreach ($columns as $column_name => $column) {
      if (isset($column['default']) && $column['default'] === FALSE) {
        unset($columns[$column_name]);
      }
    }

    return $columns;
  }

  /**
   * {@inheritdoc}
   */
  public function getColumns(YamlFormInterface $yamlform = NULL, EntityInterface $source_entity = NULL, AccountInterface $account = NULL, $include_elements = TRUE) {
    $view_any = ($yamlform && $yamlform->access('submission_view_any')) ? TRUE : FALSE;

    $columns = [];

    // Serial number.
    $columns['serial'] = [
      'title' => t('#'),
    ];

    // Submission ID.
    $columns['sid'] = [
      'title' => t('SID'),
      'default' => FALSE,
    ];

    // UUID.
    $columns['uuid'] = [
      'title' => t('UUID'),
      'default' => FALSE,
    ];

    // Sticky (Starred/Unstarred).
    if (empty($account)) {
      $columns['sticky'] = [
        'title' => t('Starred'),
      ];

      // Notes.
      $columns['notes'] = [
        'title' => t('Notes'),
      ];
    }

    // Created.
    $columns['created'] = [
      'title' => t('Created'),
    ];

    // Completed.
    $columns['completed'] = [
      'title' => t('Completed'),
      'default' => FALSE,
    ];

    // Changed.
    $columns['changed'] = [
      'title' => t('Changed'),
      'default' => FALSE,
    ];

    // Source entity.
    if ($view_any && empty($source_entity)) {
      $columns['entity'] = [
        'title' => t('Submitted to'),
        'sort' => FALSE,
      ];
    }

    // Submitted by.
    if (empty($account)) {
      $columns['uid'] = [
        'title' => t('User'),
      ];
    }

    // Submission language.
    if ($view_any && \Drupal::moduleHandler()->moduleExists('language')) {
      $columns['langcode'] = [
        'title' => t('Language'),
      ];
    }

    // Remote address.
    $columns['remote_addr'] = [
      'title' => t('IP address'),
    ];

    // Form.
    if (empty($yamlform) && empty($source_entity)) {
      $columns['yamlform_id'] = [
        'title' => t('Form'),
      ];
    }

    // Form elements.
    if ($yamlform && $include_elements) {
      /** @var \Drupal\yamlform\YamlFormElementManagerInterface $element_manager */
      $element_manager = \Drupal::service('plugin.manager.yamlform.element');

      $elements = $yamlform->getElementsFlattenedAndHasValue();
      foreach ($elements as $element) {
        /** @var \Drupal\yamlform\YamlFormElementInterface $element_handler */
        $element_handler = $element_manager->createInstance($element['#type']);
        $columns += $element_handler->getTableColumn($element);
      }
    }

    // Operations.
    if (empty($account)) {
      $columns['operations'] = [
        'title' => t('Operations'),
        'sort' => FALSE,
      ];
    }

    // Add name and format to all columns.
    foreach ($columns as $name => &$column) {
      $column['name'] = $name;
      $column['format'] = 'value';
    }

    return $columns;
  }

  /**
   * {@inheritdoc}
   */
  public function getCustomSetting($name, $default, YamlFormInterface $yamlform = NULL, EntityInterface $source_entity = NULL) {
    // Return the default value is form and source entity is not defined.
    if (!$yamlform && !$source_entity) {
      return $default;
    }

    $key = "results.custom.$name";
    if (!$source_entity) {
      return $yamlform->getState($key, $default);
    }

    $source_key = $source_entity->getEntityTypeId() . '.' . $source_entity->id();
    if ($yamlform->hasState("$key.$source_key")) {
      return $yamlform->getState("$key.$source_key", $default);
    }
    if ($yamlform->getState("results.custom.default", FALSE)) {
      return $yamlform->getState($key, $default);
    }
    else {
      return $default;
    }
  }

  /****************************************************************************/
  // Invoke YamlFormElement and YamlFormHandler plugin methods.
  /****************************************************************************/

  /**
   * {@inheritdoc}
   */
  public function create(array $values = []) {
    /** @var \Drupal\yamlform\YamlFormSubmissionInterface $entity */
    // Pre create is called via the YamlFormSubmission entity.
    // @see: \Drupal\yamlform\Entity\YamlFormSubmission::preCreate
    $entity = parent::create($values);

    $this->invokeYamlFormElements('postCreate', $entity);
    $this->invokeYamlFormHandlers('postCreate', $entity);

    return $entity;
  }

  /**
   * {@inheritdoc}
   */
  protected function postLoad(array &$entities) {
    /** @var \Drupal\yamlform\YamlFormSubmissionInterface $entity */
    $return = parent::postLoad($entities);
    foreach ($entities as $entity) {
      $this->invokeYamlFormElements('postLoad', $entity);
      $this->invokeYamlFormHandlers('postLoad', $entity);
    }
    return $return;
  }

  /**
   * {@inheritdoc}
   */
  protected function doPreSave(EntityInterface $entity) {
    /** @var \Drupal\yamlform\YamlFormSubmissionInterface $entity */
    $id = parent::doPreSave($entity);
    $this->invokeYamlFormElements('preSave', $entity);
    $this->invokeYamlFormHandlers('preSave', $entity);
    return $id;
  }

  /**
   * {@inheritdoc}
   */
  protected function doSave($id, EntityInterface $entity) {
    /** @var \Drupal\yamlform\YamlFormSubmissionInterface $entity */
    if ($entity->getYamlForm()->getSetting('results_disabled')) {
      return YamlFormSubmissionStorageInterface::SAVED_DISABLED;
    }

    $is_new = $entity->isNew();

    if (!$entity->serial()) {
      $entity->set('serial', $this->getNextSerial($entity));
    }

    $result = parent::doSave($id, $entity);

    // Save data.
    $this->saveData($entity, !$is_new);

    // DEBUG: dsm($entity->getState());
    // Log transaction.
    $yamlform = $entity->getYamlForm();
    $context = [
      '@id' => $entity->id(),
      '@form' => $yamlform->label(),
      'link' => $entity->toLink(t('Edit'), 'edit-form')->toString(),
    ];
    switch ($entity->getState()) {
      case YamlFormSubmissionInterface::STATE_DRAFT;
        \Drupal::logger('yamlform')->notice('@form:Submission #@id draft saved.', $context);
        break;

      case YamlFormSubmissionInterface::STATE_UPDATED;
        \Drupal::logger('yamlform')->notice('@form:Submission #@id updated.', $context);
        break;

      case YamlFormSubmissionInterface::STATE_COMPLETED;
        if ($result === SAVED_NEW) {
          \Drupal::logger('yamlform')->notice('@form:Submission #@id created.', $context);
        }
        else {
          \Drupal::logger('yamlform')->notice('@form:Submission #@id completed.', $context);
        }
        break;
    }

    return $result;
  }

  /**
   * Returns the next serial number.
   *
   * @return int
   *   The next serial number.
   */
  protected function getNextSerial(YamlFormSubmissionInterface $yamlform_submission) {
    $yamlform = $yamlform_submission->getYamlForm();

    $next_serial = $yamlform->getState('next_serial');
    $max_serial = $this->getMaxSerial($yamlform);
    $serial = max($next_serial, $max_serial);

    $yamlform->setState('next_serial', $serial + 1);

    return $serial;
  }

  /**
   * {@inheritdoc}
   */
  public function getMaxSerial(YamlFormInterface $yamlform) {
    $query = db_select('yamlform_submission');
    $query->condition('yamlform_id', $yamlform->id());
    $query->addExpression('MAX(serial)');
    return $query->execute()->fetchField() + 1;
  }

  /**
   * {@inheritdoc}
   */
  protected function doPostSave(EntityInterface $entity, $update) {
    /** @var \Drupal\yamlform\YamlFormSubmissionInterface $entity */
    parent::doPostSave($entity, $update);
    $this->invokeYamlFormElements('postSave', $entity, $update);
    $this->invokeYamlFormHandlers('postSave', $entity, $update);
  }

  /**
   * {@inheritdoc}
   */
  public function delete(array $entities) {
    /** @var \Drupal\yamlform\YamlFormSubmissionInterface $entity */
    if (!$entities) {
      // If no entities were passed, do nothing.
      return;
    }

    foreach ($entities as $entity) {
      $this->invokeYamlFormElements('preDelete', $entity);
      $this->invokeYamlFormHandlers('preDelete', $entity);
    }

    $return = parent::delete($entities);
    $this->deleteData($entities);

    foreach ($entities as $entity) {
      $this->invokeYamlFormElements('postDelete', $entity);
      $this->invokeYamlFormHandlers('postDelete', $entity);
    }

    // Log deleted.
    foreach ($entities as $entity) {
      \Drupal::logger('yamlform')
        ->notice('Deleted @form:Submission #@id.', [
          '@id' => $entity->id(),
          '@form' => $entity->getYamlForm()->label(),
        ]);
    }

    return $return;
  }

  /**
   * {@inheritdoc}
   */
  public function invokeYamlFormHandlers($method, YamlFormSubmissionInterface $yamlform_submission, &$context1 = NULL, &$context2 = NULL) {
    $yamlform = $yamlform_submission->getYamlForm();
    $yamlform->invokeHandlers($method, $yamlform_submission, $context1, $context2);
  }

  /**
   * {@inheritdoc}
   */
  public function invokeYamlFormElements($method, YamlFormSubmissionInterface $yamlform_submission, &$context1 = NULL, &$context2 = NULL) {
    $yamlform = $yamlform_submission->getYamlForm();
    $yamlform->invokeElements($method, $yamlform_submission, $context1, $context2);
  }

  /****************************************************************************/
  // Data handlers.
  /****************************************************************************/

  /**
   * Save form submission data from the 'yamlform_submission_data' table.
   *
   * @param array $yamlform_submissions
   *   An array of form submissions.
   */
  protected function loadData(array &$yamlform_submissions) {
    // Load form submission data.
    if ($sids = array_keys($yamlform_submissions)) {
      $result = Database::getConnection()->select('yamlform_submission_data', 'sd')
        ->fields('sd', ['yamlform_id', 'sid', 'name', 'property', 'delta', 'value'])
        ->condition('sd.sid', $sids, 'IN')
        ->orderBy('sd.sid', 'ASC')
        ->orderBy('sd.name', 'ASC')
        ->orderBy('sd.property', 'ASC')
        ->orderBy('sd.delta', 'ASC')
        ->execute();
      $submissions_data = [];
      while ($record = $result->fetchAssoc()) {
        $sid = $record['sid'];
        $name = $record['name'];

        $elements = $yamlform_submissions[$sid]->getYamlForm()->getElementsFlattenedAndHasValue();
        $element = (isset($elements[$name])) ? $elements[$name] : ['#yamlform_multiple' => FALSE, '#yamlform_composite' => FALSE];

        if ($element['#yamlform_multiple']) {
          $submissions_data[$sid][$name][$record['delta']] = $record['value'];
        }
        elseif ($element['#yamlform_composite']) {
          $submissions_data[$sid][$name][$record['property']] = $record['value'];
        }
        else {
          $submissions_data[$sid][$name] = $record['value'];
        }
      }

      // Set form submission data via setData().
      foreach ($submissions_data as $sid => $submission_data) {
        $yamlform_submissions[$sid]->setData($submission_data);
        $yamlform_submissions[$sid]->setOriginalData($submission_data);
      }
    }
  }

  /**
   * Save form submission data to the 'yamlform_submission_data' table.
   *
   * @param \Drupal\yamlform\YamlFormSubmissionInterface $yamlform_submission
   *   A form submission.
   * @param bool $delete_first
   *   TRUE to delete any data first. For new submissions this is not needed.
   */
  protected function saveData(YamlFormSubmissionInterface $yamlform_submission, $delete_first = TRUE) {
    // Get submission data rows.
    $data = $yamlform_submission->getData();
    $yamlform_id = $yamlform_submission->getYamlForm()->id();
    $sid = $yamlform_submission->id();

    $elements = $yamlform_submission->getYamlForm()->getElementsFlattenedAndHasValue();

    $rows = [];
    foreach ($data as $name => $item) {
      $element = (isset($elements[$name])) ? $elements[$name] : ['#yamlform_multiple' => FALSE, '#yamlform_composite' => FALSE];
      if ($element['#yamlform_multiple']) {
        if (is_array($item)) {
          foreach ($item as $delta => $value) {
            $rows[] = [
              'yamlform_id' => $yamlform_id,
              'sid' => $sid,
              'name' => $name,
              'property' => '',
              'delta' => $delta,
              'value' => (string) $value,
            ];
          }
        }
      }
      elseif ($element['#yamlform_composite']) {
        if (is_array($item)) {
          foreach ($item as $property => $value) {
            $rows[] = [
              'yamlform_id' => $yamlform_id,
              'sid' => $sid,
              'name' => $name,
              'property' => $property,
              'delta' => 0,
              'value' => (string) $value,
            ];
          }
        }
      }
      else {
        $rows[] = [
          'yamlform_id' => $yamlform_id,
          'sid' => $sid,
          'name' => $name,
          'property' => '',
          'delta' => 0,
          'value' => (string) $item,
        ];
      }
    }

    if ($delete_first) {
      // Delete existing submission data rows.
      $this->database->delete('yamlform_submission_data')
        ->condition('sid', $sid)
        ->execute();
    }

    // Insert new submission data rows.
    $query = $this->database
      ->insert('yamlform_submission_data')
      ->fields(['yamlform_id', 'sid', 'name', 'property', 'delta', 'value']);
    foreach ($rows as $row) {
      $query->values($row);
    }
    $query->execute();
  }

  /**
   * Delete form submission data fromthe 'yamlform_submission_data' table.
   *
   * @param array $yamlform_submissions
   *   An array of form submissions.
   */
  protected function deleteData(array $yamlform_submissions) {
    Database::getConnection()->delete('yamlform_submission_data')
      ->condition('sid', array_keys($yamlform_submissions), 'IN')
      ->execute();
  }

}
