<?php

/**
 * @file
 * Contains Drupal\yamlform\Entity\YamlForm.
 */

namespace Drupal\yamlform\Entity;

use Drupal\Component\Serialization\Yaml;
use Drupal\Component\Utility\Xss;
use Drupal\Core\Config\Entity\ConfigEntityBundleBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Render\Element;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\yamlform\YamlFormHandlerInterface;
use Drupal\yamlform\YamlFormHandlerPluginCollection;
use Drupal\yamlform\YamlFormInterface;
use Drupal\yamlform\YamlFormSubmissionInterface;

/**
 * Defines the YAML form entity.
 *
 * @ConfigEntityType(
 *   id = "yamlform",
 *   label = @Translation("YAML form"),
 *   handlers = {
 *     "view_builder" = "Drupal\yamlform\YamlFormEntityViewBuilder",
 *     "list_builder" = "Drupal\yamlform\YamlFormEntityListBuilder",
 *     "access" = "Drupal\yamlform\YamlFormEntityAccessControlHandler",
 *     "form" = {
 *       "default" = "Drupal\yamlform\YamlFormEntityForm",
 *       "settings" = "Drupal\yamlform\YamlFormEntitySettingsForm",
 *       "third_party_settings" = "Drupal\yamlform\YamlFormEntityThirdPartySettingsForm",
 *       "access" = "Drupal\yamlform\YamlFormEntityAccessForm",
 *       "handlers" = "Drupal\yamlform\YamlFormEntityHandlersForm",
 *       "delete" = "Drupal\Core\Entity\EntityDeleteForm",
 *       "export" = "Drupal\yamlform\Form\YamlFormExportForm",
 *       "duplicate" = "Drupal\yamlform\YamlFormEntityForm",
 *     }
 *   },
 *   admin_permission = "administer yamlform",
 *   bundle_of = "yamlform_submission",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "title",
 *   },
 *   links = {
 *     "canonical" = "/yamlform/{yamlform}",
 *     "submissions" = "/yamlform/{yamlform}/submissions",
 *     "add-form" = "/yamlform/{yamlform}",
 *     "test-form" = "/yamlform/{yamlform}/test",
 *     "edit-form" = "/admin/structure/yamlform/manage/{yamlform}",
 *     "settings-form" = "/admin/structure/yamlform/manage/{yamlform}/settings",
 *     "third-party-settings-form" = "/admin/structure/yamlform/manage/{yamlform}/third-party",
 *     "access-form" = "/admin/structure/yamlform/manage/{yamlform}/access",
 *     "handlers-form" = "/admin/structure/yamlform/manage/{yamlform}/handlers",
 *     "duplicate-form" = "/admin/structure/yamlform/manage/{yamlform}/duplicate",
 *     "delete-form" = "/admin/structure/yamlform/manage/{yamlform}/delete",
 *     "export-form" = "/admin/structure/yamlform/manage/{yamlform}/export",
 *     "results-submissions" = "/admin/structure/yamlform/manage/{yamlform}/results/submissions",
 *     "results-table" = "/admin/structure/yamlform/manage/{yamlform}/results/table",
 *     "results-export" = "/admin/structure/yamlform/manage/{yamlform}/results/download",
 *     "results-clear" = "/admin/structure/yamlform/manage/{yamlform}/results/clear",
 *     "collection" = "/admin/structure/yamlform",
 *   },
 *   config_export = {
 *     "status",
 *     "id",
 *     "uuid",
 *     "title",
 *     "description",
 *     "inputs",
 *     "settings",
 *     "access",
 *     "handlers",
 *     "third_party_settings",
 *   },
 * )
 */
class YamlForm extends ConfigEntityBundleBase implements YamlFormInterface {

  use StringTranslationTrait;

  /**
   * The YAML form ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The YAML form UUID.
   *
   * @var string
   */
  protected $uuid;

  /**
   * The YAML form status.
   *
   * @var boolean
   */
  protected $status = 1;

  /**
   * The YAML form title.
   *
   * @var string
   */
  protected $title;

  /**
   * The YAML form description.
   *
   * @var string
   */
  protected $description;

  /**
   * The YAML form settings.
   *
   * @var array
   */
  protected $settings = [];

  /**
   * The YAML form access controls.
   *
   * @var array
   */
  protected $access = [];


  /**
   * The YAML form original inputs.
   *
   * @var string
   */
  protected $originalInputs;

  /**
   * The YAML form inputs.
   *
   * @var string
   */
  protected $inputs;

  /**
   * The array of YAML form handlers for this YAML form.
   *
   * @var array
   */
  protected $handlers = [];

  /**
   * Holds the collection of YAML form handlers that are used by this YAML form.
   *
   * @var \Drupal\yamlform\YamlFormHandlerPluginCollection
   */
  protected $handlersCollection;

  /**
   * The YAML form input inputs decoded.
   *
   * @var array
   */
  protected $inputsDecoded;

  /**
   * The YAML form input flattened.
   *
   * @var array
   */
  protected $flattenedInputs;

  /**
   * The YAML form elements.
   *
   * @var array
   */
  protected $elements;

  /**
   * Track if the form has a managed file (upload) element.
   *
   * @var bool
   */
  protected $hasManagedFile = FALSE;

  /**
   * {@inheritdoc}
   */
  public function isOpen() {
    return $this->status ? TRUE : FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function isClosed() {
    return !$this->isOpen();
  }

  /**
   * Checks if a YAML form has submissions.
   *
   * @return bool
   *   TRUE if the YAML form has submissions.
   */
  public function hasSubmissions() {
    /** @var \Drupal\yamlform\YamlFormSubmissionStorageInterface $submission_storage */
    $submission_storage = \Drupal::entityManager()->getStorage('yamlform_submission');
    return ($submission_storage->getTotal($this)) ? TRUE : FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function hasPage() {
    $settings = $this->getSettings();
    return $settings['page'] ? TRUE : FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function hasManagedFile() {
    $this->initInputs();
    return $this->hasManagedFile;
  }

  /**
   * {@inheritdoc}
   */
  public static function getIgnoredProperties() {
    return [
      // Properties that will break YAML form data handling.
      '#tree' => '#tree',
      '#array_parents' => '#array_parents',
      // Properties that will cause unpredictable rendering.
      '#weight' => '#weight',
      // Callbacks are blocked to prevent unwanted code executions.
      '#after_build' => '#after_build',
      '#element_validate' => '#element_validate',
      '#post_render' => '#post_render',
      '#pre_render' => '#pre_render',
      '#process' => '#process',
      '#submit' => '#submit',
      '#validate' => '#validate',
      '#value_callback' => '#value_callback',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getSettings() {
    return $this->settings + self::getDefaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function setSettings(array $settings) {
    $this->settings = $settings + self::getDefaultSettings();
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getSetting($key) {
    $settings = $this->getSettings();
    return (isset($settings[$key])) ? $settings[$key] : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function setSetting($key, $value) {
    $settings = $this->getSettings();
    $settings[$key] = $value;
    $this->setSettings($settings);
  }

  /**
   * {@inheritdoc}
   */
  public function getAccessRules() {
    return $this->access;
  }

  /**
   * {@inheritdoc}
   */
  public function setAccessRules(array $access) {
    $this->access = $access;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function getDefaultSettings() {
    return [
      'page' => TRUE,
      'page_submit_path' => '',
      'page_confirm_path' => '',
      'form_submit_label' => '',
      'form_exception_message' => '',
      'form_closed_message' => '',
      'form_prepopulate' => FALSE,
      'preview' => DRUPAL_DISABLED,
      'preview_next_button_label' => '',
      'preview_prev_button_label' => '',
      'preview_message' => '',
      'draft' => FALSE,
      'draft_auto_save' => FALSE,
      'draft_button_label' => '',
      'draft_saved_message' => '',
      'draft_loaded_message' => '',
      'confirmation_type' => 'page',
      'confirmation_message' => '',
      'confirmation_url' => '',
      'limit_total' => NULL,
      'limit_total_message' => '',
      'limit_user' => NULL,
      'limit_user_message' => '',
      'results_disabled' => '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function getDefaultAccessRules() {
    return [
      'create' => [
        'roles' => [
          'anonymous',
          'authenticated',
        ],
        'users' => [],
      ],
      'view_any' => [
        'roles' => [],
        'users' => [],
      ],
      'update_any' => [
        'roles' => [],
        'users' => [],
      ],
      'delete_any' => [
        'roles' => [],
        'users' => [],
      ],
      'purge_any' => [
        'roles' => [],
        'users' => [],
      ],
      'view_own' => [
        'roles' => [],
        'users' => [],
      ],
      'update_own' => [
        'roles' => [],
        'users' => [],
      ],
      'delete_own' => [
        'roles' => [],
        'users' => [],
      ],
    ];
  }
  /**
   * {@inheritdoc}
   */
  public function checkAccessRules($operation, AccountInterface $account, YamlFormSubmissionInterface $yamlform_submission = NULL) {
    // Always grant access to "admin" which are YAML form and YAML form submission administrators.
    if ($account->hasPermission('administer yamlform') || $account->hasPermission('administer yamlform submission')) {
      return TRUE;
    }

    // The "page" operation is the same as "create" but requires that the
    // YAML form is allowed to be displayed as dedicated page.
    // Used by the 'entity.yamlform.canonical' route.
    if ($operation == 'page') {
      if (empty($this->settings['page'])) {
        return FALSE;
      }
      else {
        $operation = 'create';
      }
    }
    $access_rules = $this->getAccessRules();

    if (isset($access_rules[$operation])
      && in_array($operation, ['create', 'view_any', 'update_any', 'delete_any', 'purge_any', 'view_own'])
      && $this->checkAccessRule($access_rules[$operation], $account)) {
      return TRUE;
    }
    elseif (isset($access_rules[$operation . '_any'])
      && $this->checkAccessRule($access_rules[$operation . '_any'], $account)) {
      return TRUE;
    }
    elseif (isset($access_rules[$operation . '_own'])
      && $account->isAuthenticated() && $yamlform_submission
      && $account->id() === $yamlform_submission->getOwnerId()
      && $this->checkAccessRule($access_rules[$operation . '_own'], $account)) {
      return TRUE;
    }
    else {
      return FALSE;
    }
  }

  /**
   * Checks an access rule against a user account's roles and id.
   *
   * @param array $access_rule
   *   An access rule.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user session for which to check access.
   *
   * @return bool
   *   The access result. Returns a TRUE if access is allowed.
   */
  protected function checkAccessRule(array $access_rule, AccountInterface $account) {
    if (!empty($access_rule['roles']) && array_intersect($access_rule['roles'], $account->getRoles())) {
      return TRUE;
    }
    elseif (!empty($access_rule['users']) && in_array($account->id(), $access_rule['users'])) {
      return TRUE;
    }
    else {
      return FALSE;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getSubmissionForm(array $values = []) {
    // Set this YAML form's id.
    $values['yamlform_id'] = $this->id();

    $yamlform_submission = $this->entityManager()
      ->getStorage('yamlform_submission')
      ->create($values);

    return \Drupal::service('entity.form_builder')
      ->getForm($yamlform_submission);
  }

  /**
   * {@inheritdoc}
   */
  public function getInputsRaw() {
    return $this->inputs;
  }

  /**
   * {@inheritdoc}
   */
  public function getOriginalInputsRaw() {
    return $this->originalInputs;
  }

  /**
   * {@inheritdoc}
   */
  public function getInputs() {
    $this->initInputs();
    return $this->inputsDecoded;
  }

  /**
   * {@inheritdoc}
   */
  public function getFlattenedInputs() {
    $this->initInputs();
    return $this->flattenedInputs;
  }

  /**
   * Initialize parse YAML form inputs.
   */
  protected function initInputs() {
    if (isset($this->inputsDecoded)) {
      return;
    }

    $this->flattenedInputs = [];
    try {
      $inputs = Yaml::decode($this->inputs);
      // Since YAML supports simple values.
      $inputs = (is_array($inputs)) ? $inputs : [];
    }
    catch (\Exception $exception) {
      $link = $this->link(t('Edit'), 'edit-form');
      \Drupal::logger('yamlform')
        ->notice('%title inputs are not valid. @message', [
          '%title' => $this->label(),
          '@message' => $exception->getMessage(),
          'link' => $link,
        ]);
      $inputs = FALSE;
    }

    if ($inputs !== FALSE) {
      $this->initInputsRecursive($inputs);
      $this->invokeHandlers('alterInputs', $inputs, $this);
    }

    $this->inputsDecoded = $inputs;
  }

  /**
   * Initialize YAML form inputs into a flatten array.
   *
   * @param array $elements
   *   The YAML form inputs.
   */
  protected function initInputsRecursive(array &$elements) {
    foreach ($elements as $key => &$element) {
      if (Element::property($key) || !is_array($element)) {
        continue;
      }

      // Remove ignored properties.
      $ignored_properties = self::getIgnoredProperties();
      $element = array_diff_key($element, array_flip($ignored_properties));

      // Set key.
      $element['#key'] = $key;

      // Set title to NULL if is is not defined.
      if (!isset($element['#title'])) {
        $element['#title'] = NULL;
      }

      // Set #allowed_tags to admin tag list. YAML form builders are
      // to be consider trusted users.
      if (isset($element['#allowed_tags'])) {
        $element['#allowed_tags'] = Xss::getAdminTagList();
      }

      // Set element options.
      if (isset($element['#options'])) {
        $element['#options'] = $this->getElementOptions($element);
      }

      // If #private set #access.
      if (!empty($element['#private'])) {
        $element['#access'] = $this->access('submission_view_any');
      }

      // Track managed file upload.
      if (isset($element['#type']) && $element['#type'] == 'managed_file') {
        $this->hasManagedFile = TRUE;
      }

      $this->flattenedInputs[$key] = $element;

      $this->initInputsRecursive($element);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getElements() {
    if (!isset($this->elements)) {
      /** @var \Drupal\yamlform\YamlFormElementManager $yamlform_element_manager */
      $yamlform_element_manager = \Drupal::service('plugin.manager.yamlform.element');
      $this->elements = [];
      $inputs = $this->getFlattenedInputs();
      foreach ($inputs as $key => $element) {
        if (!empty($element['#type']) && !$yamlform_element_manager->isContainer($element)) {
          $this->elements[$key] = $element;
        }
      }
    }
    return $this->elements;
  }

  /**
   * {@inheritdoc}
   */
  public function getElementOptions(array $element) {
    if (is_array($element['#options'])) {
      return $element['#options'];
    }

    $options = [];

    // Load and alter options.
    if (!empty($element['#options']) && is_string($element['#options'])) {
      $id = $element['#options'];
      if ($yamlform_options = YamlFormOptions::load($id)) {
        $options = $yamlform_options->getOptions();
      }
      \Drupal::moduleHandler()->alter('yamlform_options', $options, $element, $id);
      \Drupal::moduleHandler()->alter('yamlform_options_' . $id, $options, $element);
    }

    // Log empty options.
    if (empty($options)) {
      $link = $this->link($this->t('Edit'), 'edit-form');
      \Drupal::logger('yamlform')->notice('%options YAML form options do not exist.', ['%form' => $this->label(), 'link' => $link]);
    }

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage, array &$values) {
    $values += [
      'settings' => self::getDefaultSettings(),
      'access' => self::getDefaultAccessRules(),
    ];
  }
  /**
   * {@inheritdoc}
   */
  public static function postLoad(EntityStorageInterface $storage, array &$entities) {
    foreach ($entities as $entity) {
      $entity->originalInputs = $entity->inputs;
    }
  }


  /**
   * {@inheritdoc}
   */
  public static function preDelete(EntityStorageInterface $storage, array $entities) {
    /** @var \Drupal\yamlform\YamlFormInterface[] $entities */
    parent::preDelete($storage, $entities);

    // Delete all submission associated with this YAML form.
    $entity_ids = \Drupal::entityQuery('yamlform_submission')
      ->condition('yamlform_id', array_keys($entities), 'IN')
      ->sort('sid')
      ->execute();
    entity_delete_multiple('yamlform_submission', $entity_ids);

    // Delete all paths and states associated with this YAML form.
    foreach ($entities as $entity) {
      // Delete all paths.
      $entity->deletePaths();

      // Delete the state.
      \Drupal::state()->delete('yamlform.' . $entity->id());
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    $cache_tags = parent::getCacheTags();
    // Add YAML form to cache tags which are used by the YamlFormSubmissionForm.
    $cache_tags[] = 'yamlform:' . $this->id();
    return $cache_tags;
  }

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage) {
    // Serialize inputs array to YAML.
    if (is_array($this->inputs)) {
      $this->inputs = Yaml::encode($this->inputs);
    }

    parent::preSave($storage);
  }

  /**
   * {@inheritdoc}
   */
  public function postSave(EntityStorageInterface $storage, $update = TRUE) {
    parent::postSave($storage, $update);

    // Update paths.
    $this->updatePaths();

    // Clear cached properties.
    $this->inputsDecoded = NULL;
    $this->flattenedInputs = NULL;
    $this->elements = NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function updatePaths() {
    // Path module must be enable for URL aliases to be updated.
    if (!\Drupal::moduleHandler()->moduleExists('path')) {
      return;
    }

    // Update submit path.
    $submit_path = $this->settings['page_submit_path'] ?: trim(\Drupal::config('yamlform.settings')->get('settings.default_page_base_path'), '/') . '/' . str_replace('_', '-', $this->id());
    $submit_source = '/yamlform/' . $this->id();
    $submit_alias = '/' . trim($submit_path, '/');
    $this->updatePath($submit_source, $submit_alias, $this->langcode);
    $this->updatePath($submit_source, $submit_alias, LanguageInterface::LANGCODE_NOT_SPECIFIED);

    // Update confirm path.
    $confirm_path = $this->settings['page_confirm_path'] ?:  $submit_path . '/confirmation';
    $confirm_source = '/yamlform/' . $this->id() . '/confirmation';
    $confirm_alias = '/' . trim($confirm_path, '/');
    $this->updatePath($confirm_source, $confirm_alias, $this->langcode);
    $this->updatePath($confirm_source, $confirm_alias, LanguageInterface::LANGCODE_NOT_SPECIFIED);

    // Update submissions path.
    $submissions_path = $submit_path . '/submissions';
    $submissions_source = '/yamlform/' . $this->id() . '/submissions';
    $submissions_alias = '/' . trim($submissions_path, '/');
    $this->updatePath($submissions_source, $submissions_alias, $this->langcode);
    $this->updatePath($submissions_source, $submissions_alias, LanguageInterface::LANGCODE_NOT_SPECIFIED);
  }

  /**
   * {@inheritdoc}
   */
  public function deletePaths() {
    /** @var \Drupal\Core\Path\AliasStorageInterface $path_alias_storage */
    $path_alias_storage = \Drupal::service('path.alias_storage');
    $path_alias_storage->delete(['source' => '/yamlform/' . $this->id()]);
    $path_alias_storage->delete(['source' => '/yamlform/' . $this->id() . '/confirmation']);
  }

  /**
   * Saves a path alias to the database.
   *
   * @param string $source
   *   The internal system path.
   * @param string $alias
   *   The URL alias.
   * @param string $langcode
   *   (optional) The language code of the alias.
   */
  protected function updatePath($source, $alias, $langcode = LanguageInterface::LANGCODE_NOT_SPECIFIED) {
    /** @var \Drupal\Core\Path\AliasStorageInterface $path_alias_storage */
    $path_alias_storage = \Drupal::service('path.alias_storage');

    $path = $path_alias_storage->load(['source' => $source, 'langcode' => $langcode]);

    // Check if the path alias is already setup.
    if ($path && ($path['alias'] == $alias)) {
      return;
    }

    $path_alias_storage->save($source, $alias, $langcode, $path['pid']);
  }

  /**
   * {@inheritdoc}
   */
  public function deleteYamlFormHandler(YamlFormHandlerInterface $handler) {
    $this->getHandlers()->removeInstanceId($handler->getHandlerId());
    $this->save();
    return $this;
  }

  /**
   * Returns the YAML form handler plugin manager.
   *
   * @return \Drupal\Component\Plugin\PluginManagerInterface
   *   The YAML form handler plugin manager.
   */
  protected function getYamlFormHandlerPluginManager() {
    return \Drupal::service('plugin.manager.yamlform.handler');
  }

  /**
   * {@inheritdoc}
   */
  public function getHandler($handler) {
    return $this->getHandlers()->get($handler);
  }

  /**
   * {@inheritdoc}
   */
  public function getHandlers($plugin_id = NULL, $status = NULL, $results = NULL) {
    if (!$this->handlersCollection) {
      $this->handlersCollection = new YamlFormHandlerPluginCollection($this->getYamlFormHandlerPluginManager(), $this->handlers);
      /** @var \Drupal\yamlform\YamlFormHandlerBase $handler */
      foreach ($this->handlersCollection as $handler) {
        // Initialize the handler and pass in the YAML form.
        $handler->init($this);
      }
      $this->handlersCollection->sort();
    }

    /** @var \Drupal\yamlform\YamlFormHandlerPluginCollection $handlers */
    $handlers = $this->handlersCollection;

    // Clone the handlers if they are being filtered.
    if (isset($plugin_id) || isset($status) || isset($results)) {
      /** @var \Drupal\yamlform\YamlFormHandlerPluginCollection $handlers */
      $handlers = clone $this->handlersCollection;
    }

    // Filter the handlers by plugin id.
    // This is used to limit track and enforce a handlers cardinality.
    if (isset($plugin_id)) {
      foreach ($handlers as $instance_id => $handler) {
        if ($handler->getPluginId() != $plugin_id) {
          $handlers->removeInstanceId($instance_id);
        }
      }
    }

    // Filter the handlers by status.
    // This is used to limit track and enforce a handlers cardinality.
    if (isset($status)) {
      foreach ($handlers as $instance_id => $handler) {
        if ($handler->getStatus() != $status) {
          $handlers->removeInstanceId($instance_id);
        }
      }
    }

    // Filter the handlers by results.
    // This is used to track is results are processed or ignored.
    if (isset($results)) {
      foreach ($handlers as $instance_id => $handler) {
        $plugin_definition = $handler->getPluginDefinition();
        if ($plugin_definition['results'] != $results) {
          $handlers->removeInstanceId($instance_id);
        }
      }
    }

    return $handlers;
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginCollections() {
    return ['handlers' => $this->getHandlers()];
  }

  /**
   * {@inheritdoc}
   */
  public function addYamlFormHandler(array $configuration) {
    $this->getHandlers()->addInstanceId($configuration['handler_id'], $configuration);
    return $configuration['handler_id'];
  }

  /**
   * {@inheritdoc}
   */
  public function invokeHandlers($method, &$data, &$context1 = NULL, &$context2 = NULL) {
    $handlers = $this->getHandlers();
    foreach ($handlers as $handler) {
      if ($handler->isEnabled()) {
        $handler->$method($data, $context1, $context2);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getState($key, $default = NULL) {
    $namespace = 'yamlform.' . $this->id();
    $values = \Drupal::state()->get($namespace, []);
    return (isset($values[$key])) ? $values[$key] : $default;
  }

  /**
   * {@inheritdoc}
   */
  public function setState($key, $value) {
    $namespace = 'yamlform.' . $this->id();
    $values = \Drupal::state()->get($namespace, []);
    $values[$key] = $value;
    \Drupal::state()->set($namespace, $values);
  }

  /**
   * {@inheritdoc}
   */
  public function deleteState($key) {
    $namespace = 'yamlform.' . $this->id();
    $values = \Drupal::state()->get($namespace, []);
    unset($values[$key]);
    \Drupal::state()->set($namespace, $values);
  }

  /**
   * {@inheritdoc}
   *
   * Overriding so that URLs pointing to YAML form default to 'canonical'
   * submission form and not the back-end 'edit-form'.
   */
  public function url($rel = 'canonical', $options = []) {
    // Do not remove this override: the default value of $rel is different.
    return parent::url($rel, $options);
  }

  /**
   * {@inheritdoc}
   *
   * Overriding so that URLs pointing to YAML form default to 'canonical'
   * submission form and not the back-end 'edit-form'.
   */
  public function toUrl($rel = 'canonical', array $options = []) {
    return parent::toUrl($rel, $options);
  }

  /**
   * {@inheritdoc}
   *
   * Overriding so that URLs pointing to YAML form default to 'canonical'
   * submission form and not the back-end 'edit-form'.
   */
  public function urlInfo($rel = 'canonical', array $options = []) {
    return parent::urlInfo($rel, $options);
  }

  /**
   * {@inheritdoc}
   *
   * Overriding so that links to YAML form default to 'canonical' submission
   * form and not the back-end 'edit-form'.
   */
  public function toLink($text = NULL, $rel = 'canonical', array $options = []) {
    return parent::toLink($text, $rel, $options);
  }

  /**
   * {@inheritdoc}
   *
   * Overriding so that links to YAML form default to 'canonical' submission
   * form and not the back-end 'edit-form'.
   */
  public function link($text = NULL, $rel = 'canonical', array $options = []) {
    return parent::link($text, $rel, $options);
  }

  /**
   * {@inheritdoc}
   */
  public function isDefaultRevision() {
    return TRUE;
  }

}
