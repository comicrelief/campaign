<?php

namespace Drupal\entity_reference_revisions\Plugin\Field\FieldType;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\TypedData\EntityDataDefinition;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Field\Plugin\Field\FieldType\EntityReferenceItem;
use Drupal\Core\Field\PreconfiguredFieldUiOptionsInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\TypedData\DataReferenceDefinition;
use Drupal\Core\TypedData\DataReferenceTargetDefinition;
use Drupal\Core\TypedData\OptionsProviderInterface;
use Drupal\entity_reference_revisions\EntityNeedsSaveInterface;

/**
 * Defines the 'entity_reference_revisions' entity field type.
 *
 * Supported settings (below the definition's 'settings' key) are:
 * - target_type: The entity type to reference. Required.
 * - target_bundle: (optional): If set, restricts the entity bundles which may
 *   may be referenced. May be set to an single bundle, or to an array of
 *   allowed bundles.
 *
 * @FieldType(
 *   id = "entity_reference_revisions",
 *   label = @Translation("Entity reference revisions"),
 *   description = @Translation("An entity field containing an entity reference."),
 *   category = @Translation("Reference revisions"),
 *   no_ui = FALSE,
 *   class = "\Drupal\entity_reference_revisions\Plugin\Field\FieldType\EntityReferenceRevisionsItem",
 *   list_class = "\Drupal\entity_reference_revisions\EntityReferenceRevisionsFieldItemList",
 *   default_formatter = "entity_reference_revisions_entity_view",
 *   default_widget = "entity_reference_revisions_autocomplete"
 * )
 */
class EntityReferenceRevisionsItem extends EntityReferenceItem implements OptionsProviderInterface, PreconfiguredFieldUiOptionsInterface {

  /**
   * {@inheritdoc}
   */
  public function storageSettingsForm(array &$form, FormStateInterface $form_state, $has_data) {

    $entity_types = \Drupal::entityTypeManager()->getDefinitions();
    $options = array();
    foreach ($entity_types as $entity_type) {
      if ($entity_type->isRevisionable()) {
        $options[$entity_type->id()] = $entity_type->getLabel();
      }
    }

    $element['target_type'] = array(
      '#type' => 'select',
      '#title' => $this->t('Type of item to reference'),
      '#options' => $options,
      '#default_value' => $this->getSetting('target_type'),
      '#required' => TRUE,
      '#disabled' => $has_data,
      '#size' => 1,
    );

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public static function getPreconfiguredOptions() {
    $options = array();

    // Add all the commonly referenced entity types as distinct pre-configured
    // options.
    $entity_types = \Drupal::entityTypeManager()->getDefinitions();
    $common_references = array_filter($entity_types, function (EntityTypeInterface $entity_type) {
      return $entity_type->get('common_reference_revisions_target') && $entity_type->isRevisionable();
    });

    /** @var \Drupal\Core\Entity\EntityTypeInterface $entity_type */
    foreach ($common_references as $entity_type) {

      $options[$entity_type->id()] = [
        'label' => $entity_type->getLabel(),
        'field_storage_config' => [
          'settings' => [
            'target_type' => $entity_type->id(),
          ]
        ]
      ];
      $default_reference_settings = $entity_type->get('default_reference_revision_settings');
      if (is_array($default_reference_settings)) {
        $options[$entity_type->id()] = array_merge($options[$entity_type->id()], $default_reference_settings);
      }
    }

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $settings = $field_definition->getSettings();
    $target_type_info = \Drupal::entityTypeManager()->getDefinition($settings['target_type']);
    $properties = parent::propertyDefinitions($field_definition);

    if ($target_type_info->getKey('revision')) {
      $target_revision_id_definition = DataReferenceTargetDefinition::create('integer')
        ->setLabel(t('@label revision ID', array('@label' => $target_type_info->getLabel())))
        ->setSetting('unsigned', TRUE);

      $target_revision_id_definition->setRequired(TRUE);
      $properties['target_revision_id'] = $target_revision_id_definition;
    }

    $properties['entity'] = DataReferenceDefinition::create('entity_revision')
      ->setLabel($target_type_info->getLabel())
      ->setDescription(t('The referenced entity revision'))
      // The entity object is computed out of the entity ID.
      ->setComputed(TRUE)
      ->setReadOnly(FALSE)
      ->setTargetDefinition(EntityDataDefinition::create($settings['target_type']));

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    $target_type = $field_definition->getSetting('target_type');
    $target_type_info = \Drupal::entityTypeManager()->getDefinition($target_type);

    $schema = parent::schema($field_definition);

    if ($target_type_info->getKey('revision')) {
      $schema['columns']['target_revision_id'] = array(
        'description' => 'The revision ID of the target entity.',
        'type' => 'int',
        'unsigned' => TRUE,
      );
      $schema['indexes']['target_revision_id'] = array('target_revision_id');
    }

    return $schema;
  }

  /**
   * {@inheritdoc}
   */
  public function setValue($values, $notify = TRUE) {
    if (isset($values) && !is_array($values)) {
      // If either a scalar or an object was passed as the value for the item,
      // assign it to the 'entity' property since that works for both cases.
      $this->set('entity', $values, $notify);
    }
    else {
      parent::setValue($values, FALSE);
      // Support setting the field item with only one property, but make sure
      // values stay in sync if only property is passed.
      // NULL is a valid value, so we use array_key_exists().
      if (is_array($values) && array_key_exists('target_id', $values) && !isset($values['entity'])) {
        $this->onChange('target_id', FALSE);
      }
      elseif (is_array($values) && array_key_exists('target_revision_id', $values) && !isset($values['entity'])) {
        $this->onChange('target_revision_id', FALSE);
      }
      elseif (is_array($values) && !array_key_exists('target_id', $values) && !array_key_exists('target_revision_id', $values) && isset($values['entity'])) {
        $this->onChange('entity', FALSE);
      }
      elseif (is_array($values) && array_key_exists('target_id', $values) && isset($values['entity'])) {
        // If both properties are passed, verify the passed values match. The
        // only exception we allow is when we have a new entity: in this case
        // its actual id and target_id will be different, due to the new entity
        // marker.
        $entity_id = $this->get('entity')->getTargetIdentifier();
        // If the entity has been saved and we're trying to set both the
        // target_id and the entity values with a non-null target ID, then the
        // value for target_id should match the ID of the entity value.
        if (!$this->entity->isNew() && $values['target_id'] !== NULL && ($entity_id !== $values['target_id'])) {
          throw new \InvalidArgumentException('The target id and entity passed to the entity reference item do not match.');
        }
      }
      // Notify the parent if necessary.
      if ($notify && $this->getParent()) {
        $this->getParent()->onChange($this->getName());
      }
    }

  }

  /**
   * {@inheritdoc}
   */
  public function getValue() {
    $values = parent::getValue();
    if ($this->entity instanceof EntityNeedsSaveInterface && $this->entity->needsSave()) {
      $values['entity'] = $this->entity;
    }
    return $values;
  }

  /**
   * {@inheritdoc}
   */
  public function onChange($property_name, $notify = TRUE) {
    // Make sure that the target ID and the target property stay in sync.
    if ($property_name == 'entity') {
      $property = $this->get('entity');
      $target_id = $property->isTargetNew() ? NULL : $property->getTargetIdentifier();
      $this->writePropertyValue('target_id', $target_id);
      $this->writePropertyValue('target_revision_id', $property->getValue()->getRevisionId());
    }
    elseif ($property_name == 'target_id' && $this->target_id != NULL && $this->target_revision_id) {
      $this->writePropertyValue('entity', array(
        'target_id' => $this->target_id,
        'target_revision_id' => $this->target_revision_id,
      ));
    }
    elseif ($property_name == 'target_revision_id' && $this->target_revision_id && $this->target_id) {
      $this->writePropertyValue('entity', array(
        'target_id' => $this->target_id,
        'target_revision_id' => $this->target_revision_id,
      ));
    }
    if ($notify && isset($this->parent)) {
      $this->parent->onChange($this->name);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    // Avoid loading the entity by first checking the 'target_id'.
    if ($this->target_id !== NULL && $this->target_revision_id !== NULL) {
      return FALSE;
    }
    if ($this->entity && $this->entity instanceof EntityInterface) {
      return FALSE;
    }
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function preSave() {
    parent::preSave();
    if ($this->entity instanceof EntityNeedsSaveInterface && $this->entity->needsSave()) {
      $this->entity->save();
    }
    if ($this->entity) {
      $this->target_revision_id = $this->entity->getRevisionId();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function postSave($update) {
    parent::postSave($update);
    $needs_save = FALSE;
    // If any of entity, parent type or parent id is missing then return.
    if (!$this->entity || !$this->entity->getEntityType()->get('entity_revision_parent_type_field') || !$this->entity->getEntityType()->get('entity_revision_parent_id_field')) {
      return;
    }

    $entity = $this->entity;
    $parent_entity = $this->getEntity();

    // If the entity has a parent field name get the key.
    if ($entity->getEntityType()->get('entity_revision_parent_field_name_field')) {
      $parent_field_name = $entity->getEntityType()->get('entity_revision_parent_field_name_field');

      // If parent field name has changed then set it.
      if ($entity->get($parent_field_name)->value != $this->getFieldDefinition()->getName()) {
        $entity->set($parent_field_name, $this->getFieldDefinition()->getName());
        $needs_save = TRUE;
      }
    }

    $parent_type = $entity->getEntityType()->get('entity_revision_parent_type_field');
    $parent_id = $entity->getEntityType()->get('entity_revision_parent_id_field');

    // If the parent type has changed then set it.
    if ($entity->get($parent_type)->value != $parent_entity->getEntityTypeId()) {
      $entity->set($parent_type, $parent_entity->getEntityTypeId());
      $needs_save = TRUE;
    }
    // If the parent id has changed then set it.
    if ($entity->get($parent_id)->value != $parent_entity->id()) {
      $entity->set($parent_id, $parent_entity->id());
      $needs_save = TRUE;
    }

    if ($needs_save) {
      // Check if any of the keys has changed, save it, do not create a new
      // revision.
      $entity->setNewRevision(FALSE);
      $entity->save();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function delete() {
    parent::delete();
    if ($this->entity && $this->entity->getEntityType()->get('entity_revision_parent_type_field') && $this->entity->getEntityType()->get('entity_revision_parent_id_field')) {
      $this->entity->delete();
    }
}
 /**
 * {@inheritdoc}
 */
  public static function onDependencyRemoval(FieldDefinitionInterface $field_definition, array $dependencies) {
    return FALSE;
  }

}
