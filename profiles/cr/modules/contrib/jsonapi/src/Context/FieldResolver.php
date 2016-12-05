<?php

namespace Drupal\jsonapi\Context;

use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\jsonapi\Error\SerializableHttpException;

/**
 * Contains FieldResolver.
 *
 * Service which resolves public field names to and from Drupal field names.
 */
class FieldResolver implements FieldResolverInterface {

  /**
   * The entity type id.
   *
   * @var \Drupal\jsonapi\Context\CurrentContextInterface
   */
  protected $currentContext;

  /**
   * The field manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $fieldManager;

  /**
   * Creates a FieldResolver instance.
   *
   * @param \Drupal\jsonapi\Context\CurrentContextInterface $current_context
   *   The JSON API CurrentContext service.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $field_manager
   *   The field manager.
   */
  public function __construct(CurrentContextInterface $current_context, EntityFieldManagerInterface $field_manager) {
    $this->currentContext = $current_context;
    $this->fieldManager = $field_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function resolveExternal($internal_field_name) {
    // Yet to be implemented.
    return $internal_field_name;
  }

  /**
   * {@inheritdoc}
   */
  public function resolveInternal($external_field_name) {
    // Right now we are exposing all the fields with the name they have in
    // the Drupal backend. But this may change in the future.
    if (strpos($external_field_name, '.') === FALSE) {
      return $external_field_name;
    }
    // Turns 'uid.field_category.name' into
    // 'uid.entity.field_category.entity.name'. This may be too simple, but it
    // works for the time being.
    $parts = explode('.', $external_field_name);
    // The last part of the chain is the referenced field, not a relationship.
    $leave_field = array_pop($parts);
    $entity_type_id = $this->currentContext->getResourceConfig()->getEntityTypeId();
    foreach ($parts as $field_name) {
      if (!$definitions = $this->fieldManager->getFieldStorageDefinitions($entity_type_id)) {
        throw new SerializableHttpException(400, sprintf('Invalid nested filtering. There is no entity type "%s".', $entity_type_id));
      }
      if (
        empty($definitions[$field_name]) ||
        $definitions[$field_name]->getType() != 'entity_reference'
      ) {
        throw new SerializableHttpException(400, sprintf('Invalid nested filtering. Invalid entity reference "%s".', $field_name));
      }
      // Update the entity type with the referenced type.
      $entity_type_id = $definitions[$field_name]->getSetting('target_type');
    };
    // Put the leave field back before imploding.
    array_push($parts, $leave_field);
    return implode('.entity.', $parts);
  }

}
