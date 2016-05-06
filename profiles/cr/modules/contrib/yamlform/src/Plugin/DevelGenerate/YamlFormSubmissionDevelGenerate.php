<?php

/**
 * @file
 * Contains \Drupal\yamlform\Plugin\DevelGenerate\YamlFormSubmissionDevelGenerate.
 */

namespace Drupal\yamlform\Plugin\DevelGenerate;

use Drupal\Component\Serialization\Yaml;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\devel_generate\DevelGenerateBase;
use Drupal\yamlform\YamlFormSubmissionGenerate;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a YamlFormSubmissionDevelGenerate plugin.
 *
 * @DevelGenerate(
 *   id = "yamlform_submission",
 *   label = @Translation("YAML form submissions"),
 *   description = @Translation("Generate a given number of YAML form submissions. Optionally delete current submissions."),
 *   url = "yamlform",
 *   permission = "administer yamlform",
 *   settings = {
 *     "num" = 50,
 *     "kill" = FALSE,
 *   }
 * )
 */
class YamlFormSubmissionDevelGenerate extends DevelGenerateBase implements ContainerFactoryPluginInterface {

  /**
   * Track in YAML form submission are being generated.
   *
   * @var bool
   */
  protected static $generatingSubmissions = FALSE;

  /**
   * The YAML form storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $yamlformStorage;

  /**
   * The YAML form submission storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $yamlformSubmissionStorage;

  /**
   * YAML form submission generation service.
   *
   * @var \Drupal\yamlform\YamlFormSubmissionGenerate
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
   *   The YAML form storage.
   * @param \Drupal\Core\Entity\EntityStorageInterface $yamlform_submission_storage
   *   The YAML form submission storage.
   * @param \Drupal\yamlform\YamlFormSubmissionGenerate $yamlform_submission_generate
   *   The YAML form submission generator.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityStorageInterface $yamlform_storage, EntityStorageInterface $yamlform_submission_storage, YamlFormSubmissionGenerate $yamlform_submission_generate) {
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
    drupal_set_message($this->t('Please note that no emails will be sent while generating YAML form submissions.'), 'warning');
    $options = [];
    foreach ($this->yamlformStorage->loadMultiple() as $yamlform) {
      $options[$yamlform->id()] = $yamlform->label();
    }
    $form['yamlform_ids'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('YAML form'),
      '#description' => $this->t('Restrict submissions to these forms.'),
      '#required' => TRUE,
      '#options' => $options,
    ];
    $form['num'] = [
      '#type' => 'number',
      '#title' => $this->t('Number of submissions?'),
      '#min' => 0,
      '#required' => TRUE,
      '#default_value' => $this->getSetting('num'),
    ];
    $form['kill'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Delete existing submissions in specified form before generating new submissions.'),
      '#default_value' => $this->getSetting('kill'),
    ];
    return $form;
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
   *   The input values from the settings form.
   */
  protected function generateSubmissions(array $values) {
    self::$generatingSubmissions = TRUE;
    if ($values['kill']) {
      $this->deleteYamlFormSubmissions($values['yamlform_ids']);
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
   *   Array of YAML form ids.
   */
  protected function deleteYamlFormSubmissions(array $yamlform_ids) {
    $yamlforms = $this->yamlformStorage->loadMultiple($yamlform_ids);
    foreach ($yamlforms as $yamlform) {
      $this->yamlformSubmissionStorage->deleteAll($yamlform);
    }
  }

  /**
   * Add 'users' that contains a list of uids.
   *
   * @param array $values
   *   The input values from the settings form.
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
    $yamlform = $this->yamlformStorage->load($yamlform_id);

    $users = $results['users'];
    $uid = $users[array_rand($users)];

    $timestamp = rand($results['created_min'], $results['created_max']);

    $this->yamlformSubmissionStorage->create([
      'yamlform_id' => $yamlform_id,
      'entity_type' => 'yamlform',
      'entity_id' => $yamlform_id,
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
    $values = [
      'num' => array_shift($args) ?: 50,
      'kill' => drush_get_option('kill') ?: FALSE,
    ];

    if (!$this->yamlformStorage->load($yamlform_id)) {
      return drush_set_error('DEVEL_GENERATE_INVALID_INPUT', dt('Invalid YAML form name: @name', ['@name' => $yamlform_id]));
    }

    if ($this->isNumber($values['num']) == FALSE) {
      return drush_set_error('DEVEL_GENERATE_INVALID_INPUT', dt('Invalid number of submissions: @num', ['@num' => $values['num']]));
    }

    $values['yamlform_ids'] = [$yamlform_id => $yamlform_id];

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
   * Track is YAML form submissions are being generated.
   *
   * Used to block emails from being sent while using devel generate.
   *
   * @return bool
   *   TRUE is YAML form submissions are being generated.
   */
  public static function isGeneratingSubmissions() {
    return self::$generatingSubmissions;
  }

}
