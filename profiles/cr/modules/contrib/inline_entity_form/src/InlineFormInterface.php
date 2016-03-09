<?php

namespace Drupal\inline_entity_form;

use Drupal\Core\Entity\EntityHandlerInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Defines the interface for inline form handlers.
 */
interface InlineFormInterface extends EntityHandlerInterface {

  /**
   * Gets the entity type managed by this handler.
   *
   * @return \Drupal\Core\Entity\EntityTypeInterface
   *   The entity type.
   */
  public function getEntityType();

  /**
   * Gets the entity type labels (singular, plural).
   *
   * @todo Remove when #1850080 lands and IEF starts requiring Drupal 8.1.x
   *
   * @return array
   *   An array with two values:
   *     - singular: The lowercase singular label.
   *     - plural: The lowercase plural label.
   */
  public function getEntityTypeLabels();

  /**
   * Gets the label of the given entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The given entity.
   *
   * @return string
   *   The entity label.
   */
  public function getEntityLabel(EntityInterface $entity);

  /**
   * Gets the fields used to represent an entity in the IEF table.
   *
   * Modules can alter the output of this method through
   * hook_inline_entity_form_table_fields_alter().
   *
   * @param string[] $bundles
   *   An array of allowed bundles for this widget.
   *
   * @return array
   *   An array of fields keyed by field name. Each field is represented by an
   *   associative array containing the following keys:
   *   - type: 'label', 'field' or 'callback'.
   *   - label: the title of the table field's column in the IEF table.
   *   - weight: the sort order of the column in the IEF table.
   *   - display_options: (optional) used for 'field' type table fields, an
   *     array of display settings. See EntityViewBuilderInterface::viewField().
   *   - callback: for 'callback' type table fields, a callable that returns a
   *     renderable array.
   *   - callback_arguments: (optional) an array of additional arguments to pass
   *     to the callback. The entity and the theme variables are always passed
   *     as as the first two arguments.
   */
  public function getTableFields($bundles);

  /**
   * Returns the entity form to be shown through the IEF widget.
   *
   * When adding data to $form_state it should be noted that there can be
   * several IEF widgets on one master form, each with several form rows,
   * leading to possible key collisions if the keys are not prefixed with
   * $entity_form['#parents'].
   *
   * @param array $entity_form
   *   The entity form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state of the parent form.
   */
  public function entityForm($entity_form, FormStateInterface $form_state);

  /**
   * Validates the entity form.
   *
   * @param array $entity_form
   *   The entity form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state of the parent form.
   */
  public function entityFormValidate($entity_form, FormStateInterface $form_state);

  /**
   * Handles the submission of an entity form.
   *
   * Prepares the entity stored in $entity_form['#entity'] for saving by copying
   * the values from the form to matching properties and, if the entity is
   * fieldable, invoking Field API submit.
   *
   * @param array $entity_form
   *   The entity form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state of the parent form.
   */
  public function entityFormSubmit(&$entity_form, FormStateInterface $form_state);

  /**
   * Delete permanently saved entities.
   *
   * @param int[] $ids
   *   An array of entity IDs.
   * @param array $context
   *   Available keys:
   *   - parent_entity_type: The type of the parent entity.
   *   - parent_entity: The parent entity.
   */
  public function delete($ids, $context);

}
