<?php

namespace Drupal\yamlform\Plugin\DevelGenerate;

use Drupal\Component\Serialization\Yaml;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\devel_generate\DevelGenerateBase;
use Drupal\yamlform\Utility\YamlFormArrayHelper;
use Drupal\yamlform\YamlFormSubmissionGenerateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a YamlFormSubmissionDevelGenerate plugin.
 *
 * @DevelGenerate(
 *   id = "yamlform_submission",
 *   label = @Translation("Form submissions"),
 *   description = @Translation("Generate a given number of form submissions. Optionally delete current submissions."),
 *   url = "yamlform",
 *   permission = "administer yamlform",
 *   settings = {
 *     "num" = 50,
 *     "kill" = FALSE,
 *     "entity-type" = NULL,
 *     "entity-id" = NULL,
 *   }
 * )
 */
class YamlFormSubmissionDevelGenerate extends DevelGenerateBase implements ContainerFactoryPluginInterface {

  /**
   * Track in form submission are being generated.
   *
   * @var bool
   */
  protected static $generatingSubmissions = FALSE;

  /**
   * The form storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $yamlformStorage;

  /**
   * The form submission storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $yamlformSubmissionStorage;

  /**
   * Form submission generation service.
   *
   * @var \Drupal\yamlform\YamlFormSubmissionGenerateInterface
   */
  protected $yamlformSubmissionGenerate;

  /**
   * Constructs a new YamlFormSubmissionDevelGenerate object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityStorageInterface $yamlform_storage
   *   The form storage.
   * @param \Drupal\Core\Entity\EntityStorageInterface $yamlform_submission_storage
   *   The form submission storage.
   * @param \Drupal\yamlform\YamlFormSubmissionGenerateInterface $yamlform_submission_generate
   *   The form submission generator.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityStorageInterface $yamlform_storage, EntityStorageInterface $yamlform_submission_storage, YamlFormSubmissionGenerateInterface $yamlform_submission_generate) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->yamlformStorage = $yamlform_storage;
    $this->yamlformSubmissionStorage = $yamlform_submission_storage;
    $this->yamlformSubmissionGenerate = $yamlform_submission_generate;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $entity_manager = $container->get('entity.manager');
    return new static(
      $configuration, $plugin_id, $plugin_definition,
      $entity_manager->getStorage('yamlform'),
      $entity_manager->getStorage('yamlform_submission'),
      $container->get('yamlform_submission.generate')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    drupal_set_message($this->t('Please note that no emails will be sent while generating form submissions.'), 'warning');
    $options = [];
    foreach ($this->yamlformStorage->loadMultiple() as $yamlform) {
      $options[$yamlform->id()] = $yamlform->label();
    }
    $form['yamlform_ids'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Form'),
      '#description' => $this->t('Restrict submissions to these forms.'),
      '#required' => TRUE,
      '#options' => $options,
    ];
    $form['num'] = [
      '#type' => 'number',
      '#title' => $this->t('Number of submissions?'),
      '#min' => 1,
      '#required' => TRUE,
      '#default_value' => $this->getSetting('num'),
    ];
    $form['kill'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Delete existing submissions in specified form before generating new submissions.'),
      '#default_value' => $this->getSetting('kill'),
    ];
    $entity_types = \Drupal::entityManager()->getEntityTypeLabels(TRUE);
    $form['submitted'] = [
      '#type' => 'item',
      '#title' => $this->t('Submitted to'),
      '#field_prefix' => '<div class="container-inline">',
      '#field_suffix' => '</div>',
    ];
    $form['submitted']['entity-type'] = [
      '#type' => 'select',
      '#title' => $this->t('Entity type'),
      '#title_display' => 'Invisible',
      '#options' => ['' => ''] + $entity_types,
      '#default_value' => $this->getSetting('entity-type'),
    ];
    $form['submitted']['entity-id'] = [
      '#type' => 'number',
      '#title' => $this->t('Entity id'),
      '#title_display' => 'Invisible',
      '#default_value' => $this->getSetting('entity-id'),
      '#min' => 1,
      '#size' => 10,
      '#states' => [
        'invisible' => [
          ':input[name="entity-type"]' => ['value' => ''],
        ],
      ],
    ];

    $form['#validate'] = [[$this, 'validateForm']];
    return $form;
  }

  /**
   * Custom validation handler.
   */
  public function validateForm(array $form, FormStateInterface $form_state) {
    $yamlform_ids = array_filter($form_state->getValue('yamlform_ids'));

    // Let default form validation handle requiring form ids.
    if (empty($yamlform_ids)) {
      return;
    }

    $entity_type = $form_state->getValue('entity-type');
    $entity_id = $form_state->getValue('entity-id');
    if ($entity_type || $entity_id) {
      if ($error = $this->validateEntity($yamlform_ids, $entity_type, $entity_id)) {
        $form_state->setErrorByName('entity_type', $error);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function generateElements(array $values) {
    $this->generateSubmissions($values);
  }

  /**
   * Generates submissions for a list of given forms.
   *
   * @param array $values
   *   The element values from the settings form.
   */
  protected function generateSubmissions(array $values) {
    self::$generatingSubmissions = TRUE;
    if ($values['kill']) {
      $this->deleteYamlFormSubmissions($values['yamlform_ids'], $values['entity-type'], $values['entity-id']);
      $this->setMessage($this->t('Deleted existing submissions.'));
    }
    if (!empty($values['yamlform_ids'])) {
      $this->initializeGenerate($values);
      $start = time();
      for ($i = 1; $i <= $values['num']; $i++) {
        $this->generateSubmission($values);
        if (function_exists('drush_log') && $i % drush_get_option('feedback', 1000) == 0) {
          $now = time();
          drush_log(dt('Completed @feedback submissions (@rate submissions/min)', ['@feedback' => drush_get_option('feedback', 1000), '@rate' => (drush_get_option('feedback', 1000) * 60) / ($now - $start)]), 'ok');
          $start = $now;
        }
      }
    }
    $this->setMessage($this->formatPlural($values['num'], '1 submissions created.', 'Finished creating @count submissions'));
    self::$generatingSubmissions = FALSE;
  }

  /**
   * Deletes all submissions of given forms.
   *
   * @param array $yamlform_ids
   *   Array of form ids.
   * @param string|null $entity_type
   *   A form source entity type.
   * @param int|null $entity_id
   *   A form source entity id.
   */
  protected function deleteYamlFormSubmissions(array $yamlform_ids, $entity_type = NULL, $entity_id = NULL) {
    $yamlforms = $this->yamlformStorage->loadMultiple($yamlform_ids);
    $entity = ($entity_type && $entity_id) ? entity_load($entity_type, $entity_id) : NULL;
    foreach ($yamlforms as $yamlform) {
      $this->yamlformSubmissionStorage->deleteAll($yamlform, $entity);
    }
  }

  /**
   * Add 'users' that contains a list of uids.
   *
   * @param array $values
   *   The element values from the settings form.
   */
  protected function initializeGenerate(array &$values) {
    // Set user id.
    $users = $this->getUsers();
    $users = array_merge($users, ['0']);
    $values['users'] = $users;

    // Set created min and max.
    $values['created_min'] = strtotime('-1 month');
    $values['created_max'] = time();
  }

  /**
   * Create one node. Used by both batch and non-batch code branches.
   */
  protected function generateSubmission(&$results) {
    $yamlform_id = array_rand(array_filter($results['yamlform_ids']));
    /** @var \Drupal\yamlform\YamlFormInterface  $yamlform */
    $yamlform = $this->yamlformStorage->load($yamlform_id);

    $users = $results['users'];
    $uid = $users[array_rand($users)];
    $entity_type = $results['entity-type'] ?: '';
    $entity_id = $results['entity-id'] ?: '';

    $timestamp = rand($results['created_min'], $results['created_max']);
    $this->yamlformSubmissionStorage->create([
      'yamlform_id' => $yamlform_id,
      'entity_type' => $entity_type,
      'entity_id' => $entity_id,
      'uid' => $uid,
      'remote_addr' => mt_rand(0, 255) . '.' . mt_rand(0, 255) . '.' . mt_rand(0, 255) . '.' . mt_rand(0, 255),
      'uri' => preg_replace('#^' . base_path() . '#', '/', $yamlform->toUrl()->toString()),
      'data' => Yaml::encode($this->yamlformSubmissionGenerate->getData($yamlform)),
      'created' => $timestamp,
      'changed' => $timestamp,
    ])->save();
  }

  /**
   * {@inheritdoc}
   */
  public function validateDrushParams($args) {
    $yamlform_id = array_shift($args);
    $yamlform_ids = [$yamlform_id => $yamlform_id];
    $values = [
      'yamlform_ids' => $yamlform_ids,
      'num' => array_shift($args) ?: 50,
      'kill' => drush_get_option('kill') ?: FALSE,
    ];

    if (empty($yamlform_id)) {
      return drush_set_error('DEVEL_GENERATE_INVALID_INPUT', dt('Form id required'));
    }

    if (!$this->yamlformStorage->load($yamlform_id)) {
      return drush_set_error('DEVEL_GENERATE_INVALID_INPUT', dt('Invalid form name: @name', ['@name' => $yamlform_id]));
    }

    if ($this->isNumber($values['num']) == FALSE) {
      return drush_set_error('DEVEL_GENERATE_INVALID_INPUT', dt('Invalid number of submissions: @num', ['@num' => $values['num']]));
    }

    $entity_type = drush_get_option('entity-type');
    $entity_id = drush_get_option('entity-id');
    if ($entity_type || $entity_id) {
      if ($error = $this->validateEntity($yamlform_ids, $entity_type, $entity_id)) {
        return drush_set_error('DEVEL_GENERATE_INVALID_INPUT', $error);
      }
      else {
        $values['entity-type'] = $entity_type;
        $values['entity-id'] = $entity_id;
      }
    }

    return $values;
  }

  /**
   * Retrieve 50 uids from the database.
   *
   * @return array
   *   An array of uids.
   */
  protected function getUsers() {
    $users = [];
    $result = db_query_range("SELECT uid FROM {users}", 0, 50);
    foreach ($result as $record) {
      $users[] = $record->uid;
    }
    return $users;
  }

  /**
   * Track if form submissions are being generated.
   *
   * Used to block emails from being sent while using devel generate.
   *
   * @return bool
   *   TRUE if form submissions are being generated.
   */
  public static function isGeneratingSubmissions() {
    return self::$generatingSubmissions;
  }

  /**
   * Validate form source entity type and id.
   *
   * @param array $yamlform_ids
   *   An array form ids.
   * @param string $entity_type
   *   An entity type.
   * @param int $entity_id
   *   An entity id.
   *
   * @return string
   *   An error message or NULL if there are no validation errors.
   */
  protected function validateEntity(array $yamlform_ids, $entity_type, $entity_id) {
    $t = function_exists('dt') ? 'dt' : 't';

    if (!$entity_type) {
      return $t('Entity type is required');
    }

    if (!$entity_id) {
      return $t('Entity id is required');
    }

    $dt_args = ['@entity_type' => $entity_type, '@entity_id' => $entity_id];

    $source_entity = entity_load($entity_type, $entity_id);
    if (!$source_entity) {
      return $t('Unable to load @entity_type:@entity_id', $dt_args);
    }

    $dt_args['@title'] = $source_entity->label();
    if (!method_exists($source_entity, 'hasField') || !$source_entity->hasField('yamlform')) {
      return $t("'@title' (@entity_type:@entity_id) does not have a 'yamlform' field.", $dt_args);
    }

    if (count($yamlform_ids) > 1) {
      return $t("'@title' (@entity_type:@entity_id) can only be associated with a single form.", $dt_args);
    }

    $dt_args['@yamlform_ids'] = YamlFormArrayHelper::toString($yamlform_ids, $t('or'));
    if (!in_array($source_entity->yamlform->target_id, $yamlform_ids)) {
      return $t("'@title' (@entity_type:@entity_id) does not have a '@yamlform_ids' yamlform associated with it.", $dt_args);
    }

    return NULL;
  }

}
