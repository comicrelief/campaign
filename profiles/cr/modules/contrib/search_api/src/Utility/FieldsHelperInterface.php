<?php

namespace Drupal\search_api\Utility;

use Drupal\Core\TypedData\ComplexDataDefinitionInterface;
use Drupal\Core\TypedData\ComplexDataInterface;
use Drupal\Core\TypedData\DataDefinitionInterface;
use Drupal\Core\TypedData\TypedDataInterface;
use Drupal\search_api\Datasource\DatasourceInterface;
use Drupal\search_api\IndexInterface;
use Drupal\search_api\Item\FieldInterface;

/**
 * Provides an interface for implementations of the fields helper service.
 */
interface FieldsHelperInterface {

  /**
   * Extracts specific field values from a complex data object.
   *
   * The values will be set directly on the given field objects, nothing is
   * returned.
   *
   * @param \Drupal\Core\TypedData\ComplexDataInterface $item
   *   The item from which fields should be extracted.
   * @param \Drupal\search_api\Item\FieldInterface[][] $fields
   *   An associative array, keyed by property paths, mapped to field objects
   *   with that property path.
   */
  public function extractFields(ComplexDataInterface $item, array $fields);

  /**
   * Extracts value and original type from a single piece of data.
   *
   * @param \Drupal\Core\TypedData\TypedDataInterface $data
   *   The piece of data from which to extract information.
   * @param \Drupal\search_api\Item\FieldInterface $field
   *   The field into which to put the extracted data.
   */
  public function extractField(TypedDataInterface $data, FieldInterface $field);

  /**
   * Extracts field values from a typed data object.
   *
   * @param \Drupal\Core\TypedData\TypedDataInterface $data
   *   The typed data object.
   *
   * @return array
   *   An array of values.
   */
  public function extractFieldValues(TypedDataInterface $data);

  /**
   * Retrieves a list of nested properties from a complex property.
   *
   * Takes care of including bundle-specific properties for entity reference
   * properties.
   *
   * @param \Drupal\Core\TypedData\ComplexDataDefinitionInterface $property
   *   The base definition.
   *
   * @return \Drupal\Core\TypedData\DataDefinitionInterface[]
   *   The nested properties, keyed by property name.
   */
  public function getNestedProperties(ComplexDataDefinitionInterface $property);

  /**
   * Retrieves a nested property from a list of properties.
   *
   * @param \Drupal\Core\TypedData\DataDefinitionInterface[] $properties
   *   The base properties, keyed by property name.
   * @param string $propertyPath
   *   The property path of the property to retrieve.
   *
   * @return \Drupal\Core\TypedData\DataDefinitionInterface|null
   *   The requested property, or NULL if it couldn't be found.
   */
  public function retrieveNestedProperty(array $properties, $propertyPath);

  /**
   * Retrieves the inner property definition of a compound property definition.
   *
   * This will retrieve the list item type from a list data definition or the
   * definition of the referenced data from a reference data definition.
   *
   * @param \Drupal\Core\TypedData\DataDefinitionInterface $property
   *   The original property definition.
   *
   * @return \Drupal\Core\TypedData\DataDefinitionInterface
   *   The inner property definition.
   */
  public function getInnerProperty(DataDefinitionInterface $property);

  /**
   * Determines whether a field ID is reserved for special use.
   *
   * We define all field IDs starting with "search_api_" as reserved, to be safe
   * for future additions (and from clashing with backend-defined fields).
   *
   * @param string $fieldId
   *   The field ID.
   *
   * @return bool
   *   TRUE if the field ID is reserved, FALSE if it can be used normally.
   */
  public function isFieldIdReserved($fieldId);

  /**
   * Creates a search item object.
   *
   * @param \Drupal\search_api\IndexInterface $index
   *   The item's search index.
   * @param string $id
   *   The item's (combined) ID.
   * @param \Drupal\search_api\Datasource\DatasourceInterface|null $datasource
   *   (optional) The datasource of the item. If not set, it will be determined
   *   from the ID and loaded from the index if needed.
   *
   * @return \Drupal\search_api\Item\ItemInterface
   *   A search item with the given values.
   */
  public function createItem(IndexInterface $index, $id, DatasourceInterface $datasource = NULL);

  /**
   * Creates a search item object by wrapping an existing complex data object.
   *
   * @param \Drupal\search_api\IndexInterface $index
   *   The item's search index.
   * @param \Drupal\Core\TypedData\ComplexDataInterface $originalObject
   *   The original object to wrap.
   * @param string $id
   *   (optional) The item's (combined) ID. If not set, it will be determined
   *   with the \Drupal\search_api\Datasource\DatasourceInterface::getItemId()
   *   method of $datasource. In this case, $datasource must not be NULL.
   * @param \Drupal\search_api\Datasource\DatasourceInterface|null $datasource
   *   (optional) The datasource of the item. If not set, it will be determined
   *   from the ID and loaded from the index if needed.
   *
   * @return \Drupal\search_api\Item\ItemInterface
   *   A search item with the given values.
   *
   * @throws \InvalidArgumentException
   *   Thrown if both $datasource and $id are NULL.
   */
  public function createItemFromObject(IndexInterface $index, ComplexDataInterface $originalObject, $id = NULL, DatasourceInterface $datasource = NULL);

  /**
   * Creates a new field object wrapping a field of the given index.
   *
   * @param \Drupal\search_api\IndexInterface $index
   *   The index to which this field should be attached.
   * @param string $fieldIdentifier
   *   The field identifier.
   * @param array $fieldInfo
   *   (optional) An array with further configuration for the field.
   *
   * @return \Drupal\search_api\Item\FieldInterface
   *   A new field object.
   */
  public function createField(IndexInterface $index, $fieldIdentifier, $fieldInfo = array());

  /**
   * Creates a new field on an index based on a property.
   *
   * Will find and set a new unique field identifier for the field on the index.
   *
   * @param \Drupal\search_api\IndexInterface $index
   *   The search index.
   * @param \Drupal\Core\TypedData\DataDefinitionInterface $property
   *   The data definition of the property.
   * @param string|null $datasourceId
   *   The ID of the index's datasource this property belongs to, or NULL if it
   *   is a datasource-independent property.
   * @param string $propertyPath
   *   The property's property path within the property structure of the
   *   datasource.
   * @param string|null $fieldId
   *   (optional) The identifier to use for the field. If not set, a new unique
   *   field identifier on the index will be chosen automatically.
   * @param string|null $type
   *   (optional) The type to set for the field, or NULL to determine a default
   *   type automatically.
   *
   * @return \Drupal\search_api\Item\FieldInterface
   *   A new field object for the index, based on the given property.
   *
   * @throws \Drupal\search_api\SearchApiException
   *   Thrown if no type was given and no default could be determined.
   */
  public function createFieldFromProperty(IndexInterface $index, DataDefinitionInterface $property, $datasourceId, $propertyPath, $fieldId = NULL, $type = NULL);

  /**
   * Finds a new unique field identifier on the given index.
   *
   * @param \Drupal\search_api\IndexInterface $index
   *   The search index.
   * @param string $propertyPath
   *   The property path on which the field identifier should be based. Only the
   *   last component of the property path will be considered.
   *
   * @return string
   *   A new unique field identifier on the given index.
   */
  public function getNewFieldId(IndexInterface $index, $propertyPath);

}
