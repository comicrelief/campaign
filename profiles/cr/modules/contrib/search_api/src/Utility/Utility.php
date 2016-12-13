<?php

namespace Drupal\search_api\Utility;

use Drupal\Core\TypedData\ComplexDataDefinitionInterface;
use Drupal\Core\TypedData\ComplexDataInterface;
use Drupal\Core\TypedData\DataDefinitionInterface;
use Drupal\Core\TypedData\TypedDataInterface;
use Drupal\search_api\Datasource\DatasourceInterface;
use Drupal\search_api\IndexInterface;
use Drupal\search_api\Item\FieldInterface;
use Drupal\search_api\Plugin\search_api\data_type\value\TextToken;

/**
 * Contains utility methods for the Search API.
 */
class Utility {

  /**
   * Determines whether fields of the given type contain fulltext data.
   *
   * @param string $type
   *   The type to check.
   * @param string[] $text_types
   *   (optional) An array of types to be considered as text.
   *
   * @return bool
   *   TRUE if $type is one of the specified types, FALSE otherwise.
   *
   * @deprecated Will be removed during Beta phase.
   */
  public static function isTextType($type, array $text_types = array('text')) {
    return \Drupal::getContainer()
      ->get('search_api.data_type_helper')
      ->isTextType($type, $text_types);
  }

  /**
   * Retrieves the mapping for known data types to Search API's internal types.
   *
   * @return string[]
   *   An array mapping all known (and supported) Drupal data types to their
   *   corresponding Search API data types. Empty values mean that fields of
   *   that type should be ignored by the Search API.
   *
   * @deprecated Will be removed during Beta phase.
   *
   * @see hook_search_api_field_type_mapping_alter()
   */
  public static function getFieldTypeMapping() {
    return \Drupal::getContainer()
      ->get('search_api.data_type_helper')
      ->getFieldTypeMapping();
  }

  /**
   * Retrieves the necessary type fallbacks for an index.
   *
   * @param \Drupal\search_api\IndexInterface $index
   *   The index for which to return the type fallbacks.
   *
   * @return string[]
   *   An array containing the IDs of all custom data types that are not
   *   supported by the index's current server, mapped to their fallback types.
   *
   * @deprecated Will be removed during Beta phase.
   */
  public static function getDataTypeFallbackMapping(IndexInterface $index) {
    return \Drupal::getContainer()
      ->get('search_api.data_type_helper')
      ->getDataTypeFallbackMapping($index);
  }

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
   * @param string|null $langcode
   *   (optional) The code of the language the retrieved values should have.
   *
   * @deprecated Will be removed during Beta phase.
   */
  public static function extractFields(ComplexDataInterface $item, array $fields, $langcode = NULL) {
    \Drupal::getContainer()
      ->get('search_api.fields_helper')
      ->extractFields($item, $fields, $langcode);
  }

  /**
   * Extracts value and original type from a single piece of data.
   *
   * @param \Drupal\Core\TypedData\TypedDataInterface $data
   *   The piece of data from which to extract information.
   * @param \Drupal\search_api\Item\FieldInterface $field
   *   The field into which to put the extracted data.
   *
   * @deprecated Will be removed during Beta phase.
   */
  public static function extractField(TypedDataInterface $data, FieldInterface $field) {
    \Drupal::getContainer()
      ->get('search_api.fields_helper')
      ->extractField($data, $field);
  }

  /**
   * Extracts field values from a typed data object.
   *
   * @param \Drupal\Core\TypedData\TypedDataInterface $data
   *   The typed data object.
   *
   * @return array
   *   An array of values.
   *
   * @deprecated Will be removed during Beta phase.
   */
  public static function extractFieldValues(TypedDataInterface $data) {
    return \Drupal::getContainer()
      ->get('search_api.fields_helper')
      ->extractFieldValues($data);
  }

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
   *
   * @deprecated Will be removed during Beta phase.
   */
  public static function getNestedProperties(ComplexDataDefinitionInterface $property) {
    return \Drupal::getContainer()
      ->get('search_api.fields_helper')
      ->getNestedProperties($property);
  }

  /**
   * Retrieves a nested property from a list of properties.
   *
   * @param \Drupal\Core\TypedData\DataDefinitionInterface[] $properties
   *   The base properties, keyed by property name.
   * @param string $property_path
   *   The property path of the property to retrieve.
   *
   * @return \Drupal\Core\TypedData\DataDefinitionInterface|null
   *   The requested property, or NULL if it couldn't be found.
   *
   * @deprecated Will be removed during Beta phase.
   */
  public static function retrieveNestedProperty(array $properties, $property_path) {
    return \Drupal::getContainer()
      ->get('search_api.fields_helper')
      ->retrieveNestedProperty($properties, $property_path);
  }

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
   *
   * @deprecated Will be removed during Beta phase.
   */
  public static function getInnerProperty(DataDefinitionInterface $property) {
    return \Drupal::getContainer()
      ->get('search_api.fields_helper')
      ->getInnerProperty($property);
  }

  /**
   * Determines whether a field ID is reserved for special use.
   *
   * We define all field IDs starting with "search_api_" as reserved, to be safe
   * for future additions (and from clashing with backend-defined fields).
   *
   * @param string $field_id
   *   The field ID.
   *
   * @return bool
   *   TRUE if the field ID is reserved, FALSE if it can be used normally.
   *
   * @deprecated Will be removed during Beta phase.
   */
  public static function isFieldIdReserved($field_id) {
    return \Drupal::getContainer()
      ->get('search_api.fields_helper')
      ->isFieldIdReserved($field_id);
  }

  /**
   * Creates a new search query object.
   *
   * @param \Drupal\search_api\IndexInterface $index
   *   The index on which to search.
   * @param array $options
   *   (optional) The options to set for the query. See
   *   \Drupal\search_api\Query\QueryInterface::setOption() for a list of
   *   options that are recognized by default.
   *
   * @return \Drupal\search_api\Query\QueryInterface
   *   A search query object to use.
   *
   * @deprecated Will be removed during Beta phase.
   *
   * @see \Drupal\search_api\Query\QueryInterface::create()
   */
  public static function createQuery(IndexInterface $index, array $options = array()) {
    return \Drupal::getContainer()
      ->get('search_api.query_helper')
      ->createQuery($index, $options);
  }

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
   *
   * @deprecated Will be removed during Beta phase.
   */
  public static function createItem(IndexInterface $index, $id, DatasourceInterface $datasource = NULL) {
    return \Drupal::getContainer()
      ->get('search_api.fields_helper')
      ->createItem($index, $id, $datasource);
  }

  /**
   * Creates a search item object by wrapping an existing complex data object.
   *
   * @param \Drupal\search_api\IndexInterface $index
   *   The item's search index.
   * @param \Drupal\Core\TypedData\ComplexDataInterface $original_object
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
   *
   * @deprecated Will be removed during Beta phase.
   */
  public static function createItemFromObject(IndexInterface $index, ComplexDataInterface $original_object, $id = NULL, DatasourceInterface $datasource = NULL) {
    return \Drupal::getContainer()
      ->get('search_api.fields_helper')
      ->createItemFromObject($index, $original_object, $id, $datasource);
  }

  /**
   * Creates a new field object wrapping a field of the given index.
   *
   * @param \Drupal\search_api\IndexInterface $index
   *   The index to which this field should be attached.
   * @param string $field_identifier
   *   The field identifier.
   * @param array $field_info
   *   (optional) An array with further configuration for the field.
   *
   * @return \Drupal\search_api\Item\FieldInterface
   *   A new field object.
   *
   * @deprecated Will be removed during Beta phase.
   */
  public static function createField(IndexInterface $index, $field_identifier, $field_info = array()) {
    return \Drupal::getContainer()
      ->get('search_api.fields_helper')
      ->createField($index, $field_identifier, $field_info);
  }

  /**
   * Creates a new field on an index based on a property.
   *
   * Will find and set a new unique field identifier for the field on the index.
   *
   * @param \Drupal\search_api\IndexInterface $index
   *   The search index.
   * @param \Drupal\Core\TypedData\DataDefinitionInterface $property
   *   The data definition of the property.
   * @param string|null $datasource_id
   *   The ID of the index's datasource this property belongs to, or NULL if it
   *   is a datasource-independent property.
   * @param string $property_path
   *   The property's property path within the property structure of the
   *   datasource.
   * @param string|null $field_id
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
   *
   * @deprecated Will be removed during Beta phase.
   */
  public static function createFieldFromProperty(IndexInterface $index, DataDefinitionInterface $property, $datasource_id, $property_path, $field_id = NULL, $type = NULL) {
    return \Drupal::getContainer()
      ->get('search_api.fields_helper')
      ->createFieldFromProperty($index, $property, $datasource_id, $property_path, $field_id, $type);
  }

  /**
   * Finds a new unique field identifier on the given index.
   *
   * @param \Drupal\search_api\IndexInterface $index
   *   The search index.
   * @param string $property_path
   *   The property path on which the field identifier should be based. Only the
   *   last component of the property path will be considered.
   *
   * @return string
   *   A new unique field identifier on the given index.
   *
   * @deprecated Will be removed during Beta phase.
   */
  public static function getNewFieldId(IndexInterface $index, $property_path) {
    return \Drupal::getContainer()
      ->get('search_api.fields_helper')
      ->getNewFieldId($index, $property_path);
  }

  /**
   * Creates a single text token.
   *
   * @param string $value
   *   The word or other token value.
   * @param float $score
   *   (optional) The token's score.
   *
   * @return \Drupal\search_api\Plugin\search_api\data_type\value\TextTokenInterface
   *   A text token object.
   */
  public static function createTextToken($value, $score = 1.0) {
    return new TextToken($value, (float) $score);
  }

  /**
   * Returns a deep copy of the input array.
   *
   * The behavior of PHP regarding arrays with references pointing to it is
   * rather weird. Therefore, this method should be used when making a copy of
   * such an array, or of an array containing references.
   *
   * This method will also omit empty array elements (that is, elements that
   * evaluate to FALSE according to PHP's native rules).
   *
   * @param array $array
   *   The array to copy.
   *
   * @return array
   *   A deep copy of the array.
   */
  public static function deepCopy(array $array) {
    $copy = array();
    foreach ($array as $k => $v) {
      if (is_array($v)) {
        if ($v = static::deepCopy($v)) {
          $copy[$k] = $v;
        }
      }
      elseif (is_object($v)) {
        $copy[$k] = clone $v;
      }
      elseif ($v) {
        $copy[$k] = $v;
      }
    }
    return $copy;
  }

  /**
   * Creates a combined ID from a raw ID and an optional datasource prefix.
   *
   * This can be used to created an internal item ID from a datasource ID and a
   * datasource-specific raw item ID, or a combined property path from a
   * datasource ID and a property path to identify properties index-wide.
   *
   * @param string|null $datasource_id
   *   The ID of the datasource to which the item belongs. Or NULL to return the
   *   raw ID unchanged (option included for compatibility purposes).
   * @param string $raw_id
   *   The datasource-specific raw item ID of the item (or property).
   *
   * @return string
   *   The combined ID, with the datasource prefix separated by
   *   \Drupal\search_api\IndexInterface::DATASOURCE_ID_SEPARATOR.
   */
  public static function createCombinedId($datasource_id, $raw_id) {
    if (!isset($datasource_id)) {
      return $raw_id;
    }
    return $datasource_id . IndexInterface::DATASOURCE_ID_SEPARATOR . $raw_id;
  }

  /**
   * Splits an internal ID into its two parts.
   *
   * Both internal item IDs and combined property paths are prefixed with the
   * corresponding datasource ID. This method will split these IDs up again into
   * their two parts.
   *
   * @param string $combined_id
   *   The internal ID, with an optional datasource prefix separated with
   *   \Drupal\search_api\IndexInterface::DATASOURCE_ID_SEPARATOR from the
   *   raw item ID or property path.
   *
   * @return array
   *   A numeric array, containing the datasource ID in element 0 and the raw
   *   item ID or property path in element 1. In the case of
   *   datasource-independent properties (that is, when there is no prefix),
   *   element 0 will be NULL.
   */
  public static function splitCombinedId($combined_id) {
    if (strpos($combined_id, IndexInterface::DATASOURCE_ID_SEPARATOR) !== FALSE) {
      return explode(IndexInterface::DATASOURCE_ID_SEPARATOR, $combined_id, 2);
    }
    return array(NULL, $combined_id);
  }

  /**
   * Splits a property path into two parts along a path separator (:).
   *
   * The path is split into one part with a single property name, and one part
   * with the complete rest of the property path (which might be empty).
   * Depending on $separate_last the returned single property key will be the
   * first (FALSE) or last (TRUE) property of the path.
   *
   * @param string $property_path
   *   The property path to split.
   * @param bool $separate_last
   *   (optional) If FALSE, separate the first property of the path. By default,
   *   the last property is separated from the rest.
   * @param string $separator
   *   (optional) The separator to use.
   *
   * @return string[]
   *   An array with indexes 0 and 1, 0 containing the first part of the
   *   property path and 1 the second. If $separate_last is FALSE, index 0 will
   *   always contain a single property name (without any colons) and index 1
   *   might be NULL. If $separate_last is TRUE it's the exact other way round.
   */
  public static function splitPropertyPath($property_path, $separate_last = TRUE, $separator = ':') {
    $function = $separate_last ? 'strrpos' : 'strpos';
    $pos = $function($property_path, $separator);
    if ($pos !== FALSE) {
      return array(
        substr($property_path, 0, $pos),
        substr($property_path, $pos + 1),
      );
    }

    if ($separate_last) {
      return array(NULL, $property_path);
    }
    return array($property_path, NULL);
  }

}
