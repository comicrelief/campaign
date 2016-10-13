<?php

namespace Drupal\yamlform\Entity;

use Drupal\Component\Serialization\Yaml;
use Drupal\Core\Config\Entity\ConfigEntityBundleBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Render\Element;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\user\Entity\User;
use Drupal\user\UserInterface;
use Drupal\yamlform\Utility\YamlFormElementHelper;
use Drupal\yamlform\YamlFormHandlerInterface;
use Drupal\yamlform\YamlFormHandlerPluginCollection;
use Drupal\yamlform\YamlFormInterface;
use Drupal\yamlform\YamlFormSubmissionInterface;

/**
 * Defines the form entity.
 *
 * @ConfigEntityType(
 *   id = "yamlform",
 *   label = @Translation("Form"),
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
 *     "uid",
 *     "template",
 *     "id",
 *     "uuid",
 *     "title",
 *     "description",
 *     "elements",
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
   * The form ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The form UUID.
   *
   * @var string
   */
  protected $uuid;

  /**
   * The form status.
   *
   * @var bool
   */
  protected $status = TRUE;

  /**
   * The form template indicator.
   *
   * @var bool
   */
  protected $template = FALSE;

  /**
   * The form title.
   *
   * @var string
   */
  protected $title;

  /**
   * The form description.
   *
   * @var string
   */
  protected $description;

  /**
   * The owner's uid.
   *
   * @var int
   */
  protected $uid;

  /**
   * The form settings.
   *
   * @var array
   */
  protected $settings = [];

  /**
   * The form access controls.
   *
   * @var array
   */
  protected $access = [];

  /**
   * The form elements.
   *
   * @var string
   */
  protected $elements;

  /**
   * The array of form handlers for this form.
   *
   * @var array
   */
  protected $handlers = [];

  /**
   * Holds the collection of form handlers that are used by this form.
   *
   * @var \Drupal\yamlform\YamlFormHandlerPluginCollection
   */
  protected $handlersCollection;

  /**
   * The form elements original.
   *
   * @var string
   */
  protected $elementsOriginal;

  /**
   * The form elements decoded.
   *
   * @var array
   */
  protected $elementsDecoded;

  /**
   * The form elements initializes (and decoded).
   *
   * @var array
   */
  protected $elementsInitialized;

  /**
   * The form elements decoded and flattened.
   *
   * @var array
   */
  protected $elementsDecodedAndFlattened;

  /**
   * The form elements initialized and flattened.
   *
   * @var array
   */
  protected $elementsInitializedAndFlattened;

  /**
   * The form elements flattened and has value.
   *
   * @var array
   */
  protected $elementsFlattenedAndHasValue;

  /**
   * The form pages.
   *
   * @var array
   */
  protected $pages;

  /**
   * Track if the form has a managed file (upload) element.
   *
   * @var bool
   */
  protected $hasManagedFile = FALSE;

  /**
   * Track if the form is using a flexbox layout.
   *
   * @var bool
   */
  protected $hasFlexboxLayout = FALSE;

  /**
   * Track if the form has translations.
   *
   * @var bool
   */
  protected $hasTranslations;

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->uid ? User::load($this->uid) : NULL;
  }

  /**
   * Sets the entity owner's user entity.
   *
   * @param \Drupal\user\UserInterface $account
   *   The owner user entity.
   *
   * @return $this
   */
  public function setOwner(UserInterface $account) {
    $this->uid = $account->id();
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->uid;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->uid = ($uid) ? $uid : NULL;
    return $this;
  }

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
   * {@inheritdoc}
   */
  public function isTemplate() {
    return $this->template ? TRUE : FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function isConfidential() {
    return $this->getSetting('form_confidential');
  }

  /**
   * {@inheritdoc}
   */
  public function hasSubmissions() {
    /** @var \Drupal\yamlform\YamlFormSubmissionStorageInterface $submission_storage */
    $submission_storage = \Drupal::entityTypeManager()->getStorage('yamlform_submission');
    return ($submission_storage->getTotal($this)) ? TRUE : FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function hasTranslations() {
    if (isset($this->hasTranslations)) {
      return $this->hasTranslations;
    }

    if (!\Drupal::moduleHandler()->moduleExists('locale')) {
      $this->hasTranslations = FALSE;
      return $this->hasTranslations;
    }

    /** @var \Drupal\locale\LocaleConfigManager $local_config_manager */
    $local_config_manager = \Drupal::service('locale.config_manager');
    $languages = \Drupal::languageManager()->getLanguages();
    foreach ($languages as $langcode => $language) {
      if ($local_config_manager->hasTranslation('yamlform.yamlform.' . $this->id(), $langcode)) {
        $this->hasTranslations = TRUE;
        return $this->hasTranslations;
      }
    }

    $this->hasTranslations = FALSE;
    return $this->hasTranslations;
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
    $this->initElements();
    return $this->hasManagedFile;
  }

  /**
   * {@inheritdoc}
   */
  public function hasFlexboxLayout() {
    $this->initElements();
    return $this->hasFlexboxLayout;
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->description;
  }

  /**
   * {@inheritdoc}
   */
  public function setDescription($description) {
    $this->description = $description;
    return $this;
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
    // Always apply the default settings.
    $this->settings = self::getDefaultSettings();
    // Now apply custom settings.
    foreach ($settings as $name => $value) {
      $this->settings[$name] = $value;
    }
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
    return $this;
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
      'form_confidential' => FALSE,
      'form_confidential_message' => '',
      'form_prepopulate' => FALSE,
      'form_prepopulate_source_entity' => FALSE,
      'form_novalidate' => FALSE,
      'form_autofocus' => FALSE,
      'form_details_toggle' => FALSE,
      'wizard_progress_bar' => TRUE,
      'wizard_progress_pages' => FALSE,
      'wizard_progress_percentage' => FALSE,
      'wizard_next_button_label' => '',
      'wizard_prev_button_label' => '',
      'wizard_start_label' => '',
      'wizard_complete' => TRUE,
      'wizard_complete_label' => '',
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
      'entity_limit_total' => NULL,
      'entity_limit_user' => NULL,
      'results_disabled' => '',
      'token_update' => FALSE,
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
    // Always grant access to "admin" which are form and form
    // submission administrators.
    if ($account->hasPermission('administer yamlform') || $account->hasPermission('administer yamlform submission')) {
      return TRUE;
    }

    // The "page" operation is the same as "create" but requires that the
    // Form is allowed to be displayed as dedicated page.
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
  public function getSubmissionForm(array $values = [], $operation = 'default') {
    // Set this form's id.
    $values['yamlform_id'] = $this->id();

    $yamlform_submission = $this->entityTypeManager()
      ->getStorage('yamlform_submission')
      ->create($values);

    return \Drupal::service('entity.form_builder')
      ->getForm($yamlform_submission, $operation);
  }

  /**
   * {@inheritdoc}
   */
  public function getElementsRaw() {
    return $this->elements;
  }

  /**
   * {@inheritdoc}
   */
  public function getElementsOriginalRaw() {
    return $this->elementsOriginal;
  }

  /**
   * {@inheritdoc}
   */
  public function getElementsDecoded() {
    $this->initElements();
    return $this->elementsDecoded;
  }

  /**
   * {@inheritdoc}
   */
  public function getElementsInitialized() {
    $this->initElements();
    return $this->elementsInitialized;
  }

  /**
   * {@inheritdoc}
   */
  public function getElementsInitializedAndFlattened() {
    $this->initElements();
    return $this->elementsInitializedAndFlattened;
  }

  /**
   * {@inheritdoc}
   */
  public function getElementsDecodedAndFlattened() {
    $this->initElements();
    return $this->elementsDecodedAndFlattened;
  }

  /**
   * {@inheritdoc}
   */
  public function getElementsFlattenedAndHasValue() {
    $this->initElements();
    return $this->elementsFlattenedAndHasValue;
  }

  /**
   * {@inheritdoc}
   */
  public function getElementsSelectorOptions() {
    /** @var \Drupal\yamlform\YamlFormElementManagerInterface $element_manager */
    $element_manager = \Drupal::service('plugin.manager.yamlform.element');

    $selectors = [];
    $elements = $this->getElementsInitializedAndFlattened();
    foreach ($elements as $element) {
      $element_handler = $element_manager->getElementInstance($element);
      $selectors += $element_handler->getElementSelectorOptions($element);
    }
    return $selectors;
  }

  /**
   * {@inheritdoc}
   */
  public function setElements(array $elements) {
    $this->elements = Yaml::encode($elements);
    $this->resetElements();
    return $this;
  }

  /**
   * Initialize parse form elements.
   */
  protected function initElements() {
    if (isset($this->elementsInitialized)) {
      return;
    }

    $this->elementsDecodedAndFlattened = [];
    $this->elementsInitializedAndFlattened = [];
    $this->elementsFlattenedAndHasValue = [];
    try {
      $elements = Yaml::decode($this->elements);
      // Since YAML supports simple values.
      $elements = (is_array($elements)) ? $elements : [];
      $this->elementsDecoded = $elements;
    }
    catch (\Exception $exception) {
      $link = $this->link(t('Edit'), 'edit-form');
      \Drupal::logger('yamlform')
        ->notice('%title elements are not valid. @message', [
          '%title' => $this->label(),
          '@message' => $exception->getMessage(),
          'link' => $link,
        ]);
      $elements = FALSE;
    }

    if ($elements !== FALSE) {
      $this->initElementsRecursive($elements);
      $this->invokeHandlers('alterElements', $elements, $this);
    }

    $this->elementsInitialized = $elements;
  }

  /**
   * Reset parsed and cached form elements.
   */
  protected function resetElements() {
    $this->elementsDecoded = NULL;
    $this->elementsInitialized = NULL;
    $this->elementsDecodedAndFlattened = NULL;
    $this->elementsInitializedAndFlattened = NULL;
    $this->elementsFlattenedAndHasValue = NULL;
  }

  /**
   * Initialize form elements into a flatten array.
   *
   * @param array $elements
   *   The form elements.
   */
  protected function initElementsRecursive(array &$elements, $parent = '', $depth = 0) {
    /** @var \Drupal\yamlform\YamlFormElementManagerInterface $element_manager */
    $element_manager = \Drupal::service('plugin.manager.yamlform.element');

    /** @var \Drupal\Core\Render\ElementInfoManagerInterface $element_info */
    $element_info = \Drupal::service('plugin.manager.element_info');

    // Remove ignored properties.
    $elements = YamlFormElementHelper::removeIgnoredProperties($elements);

    foreach ($elements as $key => &$element) {
      if (Element::property($key) || !is_array($element)) {
        continue;
      }

      // Copy only the element properties to decoded and flattened elements.
      $this->elementsDecodedAndFlattened[$key] = YamlFormElementHelper::getProperties($element);

      // Set id, key, parent_key, depth, and parent children.
      $element['#yamlform_id'] = $this->id() . '--' . $key;
      $element['#yamlform_key'] = $key;
      $element['#yamlform_parent_key'] = $parent;
      $element['#yamlform_parent_flexbox'] = FALSE;
      $element['#yamlform_depth'] = $depth;
      $element['#yamlform_children'] = [];
      $element['#yamlform_multiple'] = FALSE;
      $element['#yamlform_composite'] = FALSE;

      if (!empty($parent)) {
        $parent_element = $this->elementsInitializedAndFlattened[$parent];
        // Add element to the parent element's children.
        $parent_element['#yamlform_children'][$key] = $key;
        // Set #parent_flexbox to TRUE is the parent element is a
        // 'yamlform_flexbox'.
        $element['#yamlform_parent_flexbox'] = (isset($parent_element['#type']) && $parent_element['#type'] == 'yamlform_flexbox') ? TRUE : FALSE;
      }

      // Set #title and #admin_title to NULL if it is not defined.
      $element += [
        '#title' => NULL,
        '#admin_title' => NULL,
      ];

      // If #private set #access.
      if (!empty($element['#private'])) {
        $element['#access'] = $this->access('submission_view_any');
      }

      $element_handler = NULL;
      if (isset($element['#type'])) {
        // Track managed file upload.
        if ($element['#type'] == 'managed_file') {
          $this->hasManagedFile = TRUE;
        }

        // Track flexbox.
        if ($element['#type'] == 'flexbox' || $element['#type'] == 'yamlform_flexbox') {
          $this->hasFlexboxLayout = TRUE;
        }

        // Set yamlform_* prefix to #type that are using alias without yamlform_
        // namespace.
        if (!$element_info->hasDefinition($element['#type']) && $element_info->hasDefinition('yamlform_' . $element['#type'])) {
          $element['#type'] = 'yamlform_' . $element['#type'];
        }

        // Load the element's handler.
        $element_handler = $element_manager->createInstance($element['#type']);

        // Initialize the element.
        $element_handler->initialize($element);

        $element['#yamlform_multiple'] = $element_handler->hasMultipleValues($element);
        $element['#yamlform_composite'] = $element_handler->isComposite($element);
      }

      // Copy only the element properties to initialized and flattened elements.
      $this->elementsInitializedAndFlattened[$key] = YamlFormElementHelper::getProperties($element);

      // Check if element has value (aka can be exported) and add it to
      // flattened has value array.
      if ($element_handler && $element_handler->isInput($element)) {
        $this->elementsFlattenedAndHasValue[$key] =& $this->elementsInitializedAndFlattened[$key];
      }

      $this->initElementsRecursive($element, $key, $depth + 1);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getElement($key) {
    $elements_flattened = $this->getElementsInitializedAndFlattened();
    return (isset($elements_flattened[$key])) ? $elements_flattened[$key] : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getElementDecoded($key) {
    $elements = $this->getElementsDecodedAndFlattened();
    return (isset($elements[$key])) ? $elements[$key] : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getElementInitialized($key) {
    $elements = $this->getElementsInitializedAndFlattened();
    return (isset($elements[$key])) ? $elements[$key] : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function setElementProperties($key, array $properties, $parent_key = '') {
    $elements = $this->getElementsDecoded();
    // If element is was not added to elements, add it as the last element.
    if (!$this->setElementPropertiesRecursive($elements, $key, $properties, $parent_key)) {
      $elements[$key] = $properties;
    }
    $this->setElements($elements);
    return $this;
  }

  /**
   * Set element properties.
   *
   * @param array $elements
   *   An associative nested array of elements.
   * @param string $key
   *   The element's key.
   * @param array $properties
   *   An associative array of properties.
   * @param string $parent_key
   *   (optional) The element's parent key. Only used for new elements.
   *
   * @return bool
   *   TRUE when the element's properties has been set. FALSE when the element
   *   has not been found.
   */
  protected function setElementPropertiesRecursive(array &$elements, $key, array $properties, $parent_key = '') {
    foreach ($elements as $element_key => &$element) {
      if (Element::property($element_key) || !is_array($element)) {
        continue;
      }

      if ($element_key == $key) {
        $element = $properties + YamlFormElementHelper::removeProperties($element);
        return TRUE;
      }

      if ($element_key == $parent_key) {
        $element[$key] = $properties;
        return TRUE;
      }

      if ($this->setElementPropertiesRecursive($element, $key, $properties, $parent_key)) {
        return TRUE;
      }
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function deleteElement($key) {
    // Delete element from the elements render array.
    $elements = $this->getElementsDecoded();
    $sub_element_keys = $this->deleteElementRecursive($elements, $key);
    $this->setElements($elements);

    // Delete submission element key data.
    \Drupal::database()->delete('yamlform_submission_data')
      ->condition('yamlform_id', $this->id())
      ->condition('name', $sub_element_keys, 'IN')
      ->execute();
  }

  /**
   * Remove an element by key from a render array.
   *
   * @param array $elements
   *   An associative nested array of elements.
   * @param string $key
   *   The element's key.
   *
   * @return array
   *   An array containing the deleted element and sub element keys.
   */
  protected function deleteElementRecursive(array &$elements, $key) {
    foreach ($elements as $element_key => &$element) {
      if (Element::property($element_key) || !is_array($element)) {
        continue;
      }

      if ($element_key == $key) {
        $sub_element_keys = [$element_key => $element_key];
        $this->collectSubElementKeysRecursive($sub_element_keys, $element);
        unset($elements[$element_key]);
        return $sub_element_keys;
      }

      if ($sub_element_keys = $this->deleteElementRecursive($element, $key)) {
        return $sub_element_keys;
      }
    }

    return FALSE;
  }

  /**
   * Collect sub element keys from a render array.
   *
   * @param array $sub_element_keys
   *   An array to be populated with sub element keys.
   * @param array $elements
   *   A render array.
   */
  protected function collectSubElementKeysRecursive(array &$sub_element_keys, array $elements) {
    foreach ($elements as $key => &$element) {
      if (Element::property($key) || !is_array($element)) {
        continue;
      }
      $sub_element_keys[$key] = $key;
      $this->collectSubElementKeysRecursive($sub_element_keys, $element);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getPages() {
    if (!isset($this->pages)) {
      // Add form page containers.
      $this->pages = [];
      $elements = $this->getElementsInitialized();
      foreach ($elements as $key => $element) {
        if (isset($element['#type']) && $element['#type'] == 'yamlform_wizard_page') {
          $this->pages[$key] = $element;
        }
      }

      // Add preview page.
      $settings = $this->getSettings();
      if ($settings['preview'] != DRUPAL_DISABLED) {
        // If there is no start page, we must define one.
        if (empty($this->pages)) {
          $this->pages['start'] = [
            '#type' => 'yamlform_wizard_page',
            '#title' => $this->getSetting('wizard_start_label') ?: \Drupal::config('yamlform.settings')->get('settings.default_wizard_start_label'),
          ];
        }
        $this->pages['preview'] = [
          '#type' => 'yamlform_preview',
          '#title' => $this->t('Preview'),
        ];
      }
    }

    // Only add complete page, if there are some pages.
    if ($this->pages  && $this->getSetting('wizard_complete')) {
      $this->pages['complete'] = [
        '#type' => 'yamlform_wizard_page',
        '#title' => $this->getSetting('wizard_complete_label') ?: \Drupal::config('yamlform.settings')->get('settings.default_wizard_complete_label'),
      ];
    }

    return $this->pages;
  }

  /**
   * {@inheritdoc}
   */
  public function getPage($index) {
    $pages = $this->getPages();
    if (isset($pages[$index])) {
      return $pages[$index];
    }

    $keys = array_keys($pages);
    if (isset($keys[$index])) {
      return $pages[$keys[$index]];
    }

    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage, array &$values) {
    $values += [
      'uid' => \Drupal::currentUser()->id(),
      'settings' => self::getDefaultSettings(),
      'access' => self::getDefaultAccessRules(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function postLoad(EntityStorageInterface $storage, array &$entities) {
    foreach ($entities as $entity) {
      $entity->elementsOriginal = $entity->elements;
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function preDelete(EntityStorageInterface $storage, array $entities) {
    /** @var \Drupal\yamlform\YamlFormInterface[] $entities */
    parent::preDelete($storage, $entities);

    // Delete all submission associated with this form.
    $entity_ids = \Drupal::entityQuery('yamlform_submission')
      ->condition('yamlform_id', array_keys($entities), 'IN')
      ->sort('sid')
      ->execute();
    entity_delete_multiple('yamlform_submission', $entity_ids);

    // Delete all paths and states associated with this form.
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
    // Add form to cache tags which are used by the YamlFormSubmissionForm.
    $cache_tags[] = 'yamlform:' . $this->id();
    return $cache_tags;
  }

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage) {
    // Always unpublish templates.
    if ($this->isTemplate()) {
      $this->setStatus(FALSE);
    }

    // Serialize elements array to YAML.
    if (is_array($this->elements)) {
      $this->elements = Yaml::encode($this->elements);
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

    // Reset elements.
    $this->resetElements();
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
   * Returns the form handler plugin manager.
   *
   * @return \Drupal\Component\Plugin\PluginManagerInterface
   *   The form handler plugin manager.
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
        // Initialize the handler and pass in the form.
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
  public function invokeElements($method, &$data, &$context1 = NULL, &$context2 = NULL) {
    /** @var \Drupal\yamlform\YamlFormElementManagerInterface $element_manager */
    $element_manager = \Drupal::service('plugin.manager.yamlform.element');

    $elements = $this->getElementsInitializedAndFlattened();
    foreach ($elements as $element) {
      $element_manager->invokeMethod($method, $element, $data, $context1, $context2);
    }
  }

  /**
   * {@inheritdoc}
   *
   * Overriding so that URLs pointing to form default to 'canonical'
   * submission form and not the back-end 'edit-form'.
   */
  public function url($rel = 'canonical', $options = []) {
    // Do not remove this override: the default value of $rel is different.
    return parent::url($rel, $options);
  }

  /**
   * {@inheritdoc}
   *
   * Overriding so that URLs pointing to form default to 'canonical'
   * submission form and not the back-end 'edit-form'.
   */
  public function toUrl($rel = 'canonical', array $options = []) {
    return parent::toUrl($rel, $options);
  }

  /**
   * {@inheritdoc}
   *
   * Overriding so that URLs pointing to form default to 'canonical'
   * submission form and not the back-end 'edit-form'.
   */
  public function urlInfo($rel = 'canonical', array $options = []) {
    return parent::urlInfo($rel, $options);
  }

  /**
   * {@inheritdoc}
   *
   * Overriding so that links to form default to 'canonical' submission
   * form and not the back-end 'edit-form'.
   */
  public function toLink($text = NULL, $rel = 'canonical', array $options = []) {
    return parent::toLink($text, $rel, $options);
  }

  /**
   * {@inheritdoc}
   *
   * Overriding so that links to form default to 'canonical' submission
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
   */
  public function hasState($key) {
    $namespace = 'yamlform.' . $this->id();
    $values = \Drupal::state()->get($namespace, []);
    return (isset($values[$key])) ? TRUE : FALSE;
  }

  /**
   * {@inheritdoc}
   */
  protected function addDependency($type, $name) {
    // A form should never have any dependencies.
    // This prevents the scenario where a YamlFormHandler's module is
    // uninstalled and any form implementing the YamlFormHandler
    // is deleted without an error being thrown.
    return $this;
  }

  /**
   * Define empty array iterator.
   *
   * See: Issue #2759267: Undefined method YamlForm::getIterator().
   */
  public function getIterator() {
    return new \ArrayIterator([]);
  }

}
