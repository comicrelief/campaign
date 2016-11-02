<?php

namespace Drupal\diff;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Plugin\PluginFormInterface;
use Drupal\Component\Plugin\ConfigurablePluginInterface;

interface FieldReferenceInterface extends PluginFormInterface, ConfigurablePluginInterface {

  /**
   * Builds an array of entities.
   *
   * This method is responsible for transforming a FieldItemListInterface object
   * into an array of entities. The resulted array of entities is then used when
   * parsing the entity to get a clean array of fields that will be compared.
   *
   * @param FieldItemListInterface $field_items
   *   Represents an entity field.
   *
   * @return EntityInterface[]
   *   An array of entities to be compared. If an empty array is returned it
   *   means that a field is either empty or no properties need to be compared
   *   for that field.
   */
  public function getEntitiesToDiff(FieldItemListInterface $field_items);
}
