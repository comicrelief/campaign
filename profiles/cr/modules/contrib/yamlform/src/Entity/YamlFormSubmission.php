<?php

namespace Drupal\yamlform\Entity;

use Drupal\Component\Serialization\Yaml;
use Drupal\Component\Utility\Crypt;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\user\Entity\User;
use Drupal\user\UserInterface;
use Drupal\yamlform\YamlFormInterface;
use Drupal\yamlform\YamlFormSubmissionInterface;

/**
 * Defines the YamlFormSubmission entity.
 *
 * @ingroup yamlform
 *
 * @ContentEntityType(
 *   id = "yamlform_submission",
 *   label = @Translation("Form submission"),
 *   bundle_label = @Translation("Form"),
 *   handlers = {
 *     "storage" = "Drupal\yamlform\YamlFormSubmissionStorage",
 *     "storage_schema" = "Drupal\yamlform\YamlFormSubmissionStorageSchema",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "view_builder" = "Drupal\yamlform\YamlFormSubmissionViewBuilder",
 *     "list_builder" = "Drupal\yamlform\YamlFormSubmissionListBuilder",
 *     "access" = "Drupal\yamlform\YamlFormSubmissionAccessControlHandler",
 *     "form" = {
 *       "default" = "Drupal\yamlform\YamlFormSubmissionForm",
 *       "notes" = "Drupal\yamlform\YamlFormSubmissionNotesForm",
 *       "delete" = "Drupal\yamlform\Form\YamlFormSubmissionDeleteForm",
 *     },
 *   },
 *   bundle_entity_type = "yamlform",
 *   list_cache_contexts = { "user" },
 *   base_table = "yamlform_submission",
 *   admin_permission = "administer yamlform",
 *   entity_keys = {
 *     "id" = "sid",
 *     "bundle" = "yamlform_id",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/yamlform/manage/{yamlform}/submission/{yamlform_submission}",
 *     "table" = "/admin/structure/yamlform/manage/{yamlform}/submission/{yamlform_submission}/table",
 *     "text" = "/admin/structure/yamlform/manage/{yamlform}/submission/{yamlform_submission}/text",
 *     "yaml" = "/admin/structure/yamlform/manage/{yamlform}/submission/{yamlform_submission}/yaml",
 *     "edit-form" = "/admin/structure/yamlform/manage/{yamlform}/submission/{yamlform_submission}/edit",
 *     "notes-form" = "/admin/structure/yamlform/manage/{yamlform}/submission/{yamlform_submission}/notes",
 *     "resend-form" = "/admin/structure/yamlform/manage/{yamlform}/submission/{yamlform_submission}/resend",
 *     "delete-form" = "/admin/structure/yamlform/manage/{yamlform}/submission/{yamlform_submission}/delete",
 *     "collection" = "/admin/structure/yamlform/results/manage/list"
 *   },
 *   permission_granularity = "bundle"
 * )
 */
class YamlFormSubmission extends ContentEntityBase implements YamlFormSubmissionInterface {

  use EntityChangedTrait;
  use StringTranslationTrait;

  /**
   * Store a reference to the current temporary form.
   *
   * @var \Drupal\yamlform\YamlFormInterface
   *
   * @see \Drupal\yamlform\YamlFormEntityElementsValidator::validateRendering()
   */
  static protected $yamlform;

  /**
   * The data.
   *
   * @var array
   */
  protected $data = [];

  /**
   * Reference to original data loaded before any updates.
   *
   * @var array
   */
  protected $originalData = [];

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields['serial'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Serial number'))
      ->setDescription(t('The serial number of the form submission entity.'))
      ->setReadOnly(TRUE);

    $fields['sid'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Submission ID'))
      ->setDescription(t('The ID of the form submission entity.'))
      ->setReadOnly(TRUE);

    $fields['uuid'] = BaseFieldDefinition::create('uuid')
      ->setLabel(t('Submission UUID'))
      ->setDescription(t('The UUID of the form submission entity.'))
      ->setReadOnly(TRUE);

    $fields['token'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Token'))
      ->setDescription(t('A secure token used to look up a submission.'))
      ->setSetting('max_length', 255)
      ->setReadOnly(TRUE);

    $fields['uri'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Submission URI'))
      ->setDescription(t('The URI the user submitted the form.'))
      ->setSetting('max_length', 2000)
      ->setReadOnly(TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the form submission was first saved as draft or submitted.'));

    $fields['completed'] = BaseFieldDefinition::create('timestamp')
      ->setLabel(t('Completed'))
      ->setDescription(t('The time that the form submission was submitted as complete (not draft).'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the form submission was last saved (complete or draft).'));

    $fields['in_draft'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Is draft'))
      ->setDescription(t('Is this a draft of the submission?'))
      ->setDefaultValue(FALSE);

    $fields['current_page'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Current page'))
      ->setDescription(t('The current wizard page.'))
      ->setSetting('max_length', 128);

    $fields['remote_addr'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Remote IP address'))
      ->setDescription(t('The IP address of the user that submitted the form.'))
      ->setSetting('max_length', 128);

    $fields['uid'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Submitted by'))
      ->setDescription(t('The submitter.'))
      ->setSetting('target_type', 'user');

    $fields['langcode'] = BaseFieldDefinition::create('language')
      ->setLabel(t('Language'))
      ->setDescription(t('The submission language code.'));

    $fields['yamlform_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Form'))
      ->setDescription(t('The associated yamlform.'))
      ->setSetting('target_type', 'yamlform');

    $fields['entity_type'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Submitted to: Entity type'))
      ->setDescription(t('The entity type to which this submission was submitted from.'))
      ->setSetting('is_ascii', TRUE)
      ->setSetting('max_length', EntityTypeInterface::ID_MAX_LENGTH);

    // Can't use entity reference without a target type because it defaults to
    // an integer which limits reference to only content entities (and not
    // config entities like Views, Panels, etc...).
    // @see \Drupal\Core\Field\Plugin\Field\FieldType\EntityReferenceItem::propertyDefinitions()
    $fields['entity_id'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Submitted to: Entity ID'))
      ->setDescription(t('The ID of the entity of which this form submission was submitted from.'))
      ->setSetting('max_length', 255);

    $fields['sticky'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Sticky'))
      ->setDescription(t('A flag that indicate the status of the form submission.'))
      ->setDefaultValue(FALSE);

    $fields['notes'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Notes'))
      ->setDescription(t('Administrative notes about the form submission.'))
      ->setDefaultValue('');

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function serial() {
    return $this->serial->value;
  }

  /**
   * {@inheritdoc}
   */
  public function label() {
    $t_args = ['@id' => $this->serial()];
    if ($source_entity = $this->getSourceEntity()) {
      $t_args['@form'] = $source_entity->label();
    }
    else {
      $t_args['@form'] = $this->getYamlForm()->label();
    }
    return $this->t('@form: Submission #@id', $t_args);
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    if (isset($this->get('created')->value)) {
      return $this->get('created')->value;
    }
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($created) {
    $this->set('created', $created);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getChangedTime() {
    return $this->get('changed')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setChangedTime($timestamp) {
    $this->set('changed', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCompletedTime() {
    return $this->get('completed')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCompletedTime($timestamp) {
    $this->set('completed', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getNotes() {
    return $this->get('notes')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setNotes($notes) {
    $this->set('notes', $notes);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getSticky() {
    return $this->get('sticky')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setSticky($sticky) {
    $this->set('sticky', $sticky);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getRemoteAddr() {
    return $this->get('remote_addr')->value ?: $this->t('(unknown)');
  }

  /**
   * {@inheritdoc}
   */
  public function setRemoteAddr($ip_address) {
    $this->set('remote_addr', $ip_address);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCurrentPage() {
    return $this->get('current_page')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCurrentPage($current_page) {
    $this->set('current_page', $current_page);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCurrentPageTitle() {
    $current_page = $this->getCurrentPage();
    $page = $this->getYamlForm()->getPage($current_page);
    return ($page && isset($page['#title'])) ? $page['#title'] : $current_page;
  }

  /**
   * {@inheritdoc}
   */
  public function getData($key = NULL) {
    if (isset($key)) {
      return (isset($this->data[$key])) ? $this->data[$key] : NULL;
    }
    else {
      return $this->data;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function setData(array $data) {
    $this->data = $data;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getOriginalData($key = NULL) {
    if ($key !== NULL) {
      return (isset($this->originalData[$key])) ? $this->originalData[$key] : NULL;
    }
    else {
      return $this->originalData;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function setOriginalData(array $data) {
    $this->originalData = $data;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getToken() {
    return $this->token->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getYamlForm() {
    if (isset($this->yamlform_id->entity)) {
      return $this->yamlform_id->entity;
    }
    else {
      return self::$yamlform;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getSourceEntity() {
    if ($this->entity_type->value && $this->entity_id->value) {
      return entity_load($this->entity_type->value, $this->entity_id->value);
    }
    else {
      return NULL;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getSourceUrl() {
    $uri = $this->uri->value;
    if ($url = \Drupal::pathValidator()->getUrlIfValid($uri)) {
      return $url->setOption('absolute', TRUE);
    }
    elseif ($entity = $this->getSourceEntity()) {
      return $entity->toUrl()->setOption('absolute', TRUE);
    }
    else {
      return $this->getYamlForm()->toUrl()->setOption('absolute', TRUE);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getTokenUrl() {
    return $this->getSourceUrl()->setOption('query', ['token' => $this->token->value]);
  }

  /**
   * {@inheritdoc}
   */
  public function invokeYamlFormHandlers($method, &$context1 = NULL, &$context2 = NULL) {
    if ($yamlform = $this->getYamlForm()) {
      $yamlform->invokeHandlers($method, $this, $context1, $context2);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function invokeYamlFormElements($method, &$context1 = NULL, &$context2 = NULL) {
    if ($yamlform = $this->getYamlForm()) {
      $yamlform->invokeElements($method, $this, $context1, $context2);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    $user = $this->get('uid')->entity;
    if (!$user || $user->isAnonymous()) {
      $user = User::getAnonymousUser();
    }
    return $user;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('uid')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('uid', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('uid', $account->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function isDraft() {
    return $this->get('in_draft')->value ? TRUE : FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function isCompleted() {
    return $this->get('completed')->value ? TRUE : FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function isSticky() {
    return (bool) $this->get('sticky')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function hasNotes() {
    return $this->notes ? TRUE : FALSE;
  }

  /**
   * Track the state of a submission.
   *
   * @return int
   *    Either STATE_NEW, STATE_DRAFT, STATE_COMPLETED, or STATE_UPDATED,
   *   depending on the last save operation performed.
   */
  public function getState() {
    if (!$this->id()) {
      return self::STATE_UNSAVED;
    }
    elseif ($this->isDraft()) {
      return self::STATE_DRAFT;
    }
    elseif ($this->completed->value == $this->changed->value) {
      return self::STATE_COMPLETED;
    }
    else {
      return self::STATE_UPDATED;
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function urlRouteParameters($rel) {
    $uri_route_parameters = parent::urlRouteParameters($rel);
    $uri_route_parameters['yamlform'] = $this->getYamlForm()->id();
    return $uri_route_parameters;
  }

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage, array &$values) {
    if (empty($values['yamlform_id']) && empty($values['yamlform'])) {
      if (empty($values['yamlform_id'])) {
        throw new \Exception('Form id (yamlform_id) is required to create a form submission.');
      }
      elseif (empty($values['yamlform'])) {
        throw new \Exception('Form (yamlform) is required to create a form submission.');
      }
    }

    // Get temporary form entity and store it in the static
    // YamlFormSubmission::$yamlform property.
    // This could be reworked to use \Drupal\user\PrivateTempStoreFactory
    // but it might be overkill since we are just using this to validate
    // that a form's elements can be rendered.
    // @see \Drupal\yamlform\YamlFormEntityElementsValidator::validateRendering()
    // @see \Drupal\yamlform_ui\Form\YamlFormUiElementTestForm::buildForm()
    if (isset($values['yamlform']) && ($values['yamlform'] instanceof YamlFormInterface)) {
      $yamlform = $values['yamlform'];
      self::$yamlform = $values['yamlform'];
      $values['yamlform_id'] = 'temp';
    }
    else {
      /** @var \Drupal\yamlform\YamlFormInterface $yamlform */
      $yamlform = YamlForm::load($values['yamlform_id']);
      self::$yamlform = NULL;
    }

    // Get request's source entity parameter.
    /** @var \Drupal\yamlform\YamlFormRequestInterface $request_handler */
    $request_handler = \Drupal::service('yamlform.request');
    $source_entity = $request_handler->getCurrentSourceEntity('yamlform');
    $values += [
      'entity_type' => ($source_entity) ? $source_entity->getEntityTypeId() : NULL,
      'entity_id' => ($source_entity) ? $source_entity->id() : NULL,
    ];

    // Decode all data in an array.
    if (empty($values['data'])) {
      $values['data'] = [];
    }
    elseif (is_string($values['data'])) {
      $values['data'] = Yaml::decode($values['data']);
    }

    // Get default date from source entity 'yamlform' field.
    if ($values['entity_type'] && $values['entity_id']) {
      $source_entity = \Drupal::entityTypeManager()->getStorage($values['entity_type'])->load($values['entity_id']);
      if ($source_entity && method_exists($source_entity, 'hasField') && $source_entity->hasField('yamlform')) {
        foreach ($source_entity->yamlform as $item) {
          if ($item->target_id == $yamlform->id() && $item->default_data) {
            $values['data'] += Yaml::decode($item->default_data);
          }
        }
      }
    }

    // Set default uri and remote_addr.
    $current_request = \Drupal::requestStack()->getCurrentRequest();
    $values += [
      'uri' => preg_replace('#^' . base_path() . '#', '/', $current_request->getRequestUri()),
      'remote_addr' => ($yamlform && $yamlform->isConfidential()) ? '' : $current_request->getClientIp(),
    ];

    // Get default uid and langcode.
    $values += [
      'uid' => \Drupal::currentUser()->id(),
      'langcode' => \Drupal::languageManager()->getCurrentLanguage()->getId(),
    ];

    // Hard code the token.
    $values['token'] = Crypt::randomBytesBase64();

    // Set is draft.
    $values['in_draft'] = FALSE;

    $yamlform->invokeHandlers(__FUNCTION__, $values);
    $yamlform->invokeElements(__FUNCTION__, $values);
  }

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage) {
    $this->changed->value = REQUEST_TIME;
    if ($this->isDraft()) {
      $this->completed->value = NULL;
    }
    elseif (!$this->isCompleted()) {
      $this->completed->value = REQUEST_TIME;
    }

    parent::preSave($storage);
  }

  /**
   * {@inheritdoc}
   */
  public function save() {
    // Clear the remote_addr for confidential submissions.
    if ($this->getYamlForm()->isConfidential()) {
      $this->get('remote_addr')->value = '';
    }

    return parent::save();
  }

}
