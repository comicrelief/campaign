<?php

namespace Drupal\search_api\Utility;

use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\TypedData\EntityDataDefinitionInterface;
use Drupal\Core\TypedData\ComplexDataDefinitionInterface;
use Drupal\Core\TypedData\ComplexDataInterface;
use Drupal\Core\TypedData\DataDefinitionInterface;
use Drupal\Core\TypedData\DataReferenceDefinitionInterface;
use Drupal\Core\TypedData\DataReferenceInterface;
use Drupal\Core\TypedData\ListDataDefinitionInterface;
use Drupal\Core\TypedData\ListInterface;
use Drupal\Core\TypedData\TypedDataInterface;
use Drupal\search_api\Datasource\DatasourceInterface;
use Drupal\search_api\IndexInterface;
use Drupal\search_api\Item\Field;
use Drupal\search_api\Item\FieldInterface;
use Drupal\search_api\Item\Item;
use Drupal\search_api\Processor\ConfigurablePropertyInterface;
use Drupal\search_api\SearchApiException;
use Drupal\search_api\Utility\Utility;
use Symfony\Component\DependencyInjection\Container;

/**
 * Provides helper methods for dealing with Search API fields and properties.
 */
class FieldsHelper implements FieldsHelperInterface {

  /**
   * The entity field manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * The entity type bundle info service.
   *
   * @var \Drupal\Core\Entity\EntityTypeBundleInfoInterface
   */
  protected $entityBundleInfo;

  /**
   * The data type plugin manager.
   *
   * @var \Drupal\search_api\Utility\DataTypeHelperInterface
   */
  protected $dataTypeHelper;

  /**
   * Cache for the field type mapping.
   *
   * @var array|null
   *
   * @see getFieldTypeMapping()
   */
  protected $fieldTypeMapping;

  /**
   * Cache for the fallback data type mapping per index.
   *
   * @var array
   *
   * @see getDataTypeFallbackMapping()
   */
  protected $dataTypeFallbackMapping = array();

  /**
   * Constructs a FieldsHelper object.
   *
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entityFieldManager
   *   The entity field manager.
   * @param EntityTypeBundleInfoInterface $entityBundleInfo
   *   The entity type bundle info service.
   * @param \Drupal\search_api\Utility\DataTypeHelperInterface $dataTypeHelper
   *   The data type helper service.
   */
  public function __construct(EntityFieldManagerInterface $entityFieldManager, EntityTypeBundleInfoInterface $entityBundleInfo, DataTypeHelperInterface $dataTypeHelper) {
    $this->entityFieldManager = $entityFieldManager;
    $this->entityBundleInfo = $entityBundleInfo;
    $this->dataTypeHelper = $dataTypeHelper;
  }

  /**
   * {@inheritdoc}
   */
  public function extractFields(ComplexDataInterface $item, array $fields) {
    // Figure out which fields are directly on the item and which need to be
    // extracted from nested items.
    $directFields = array();
    $nestedFields = array();
    foreach (array_keys($fields) as $key) {
      if (strpos($key, ':') !== FALSE) {
        list($direct, $nested) = explode(':', $key, 2);
        $nestedFields[$direct][$nested] = $fields[$key];
      }
      else {
        $directFields[] = $key;
      }
    }
    // Extract the direct fields.
    $properties = $item->getProperties(TRUE);
    foreach ($directFields as $key) {
      if (empty($properties[$key])) {
        continue;
      }
      $data = $item->get($key);
      foreach ($fields[$key] as $field) {
        $this->extractField($data, $field);
      }
    }
    // Recurse for all nested fields.
    foreach ($nestedFields as $direct => $fieldsNested) {
      if (empty($properties[$direct])) {
        continue;
      }
      $itemNested = $item->get($direct);
      if ($itemNested instanceof DataReferenceInterface) {
        $itemNested = $itemNested->getTarget();
      }
      if ($itemNested instanceof EntityInterface) {
        $itemNested = $itemNested->getTypedData();
      }
      if ($itemNested instanceof ComplexDataInterface && !$itemNested->isEmpty()) {
        $this->extractFields($itemNested, $fieldsNested);
      }
      elseif ($itemNested instanceof ListInterface && !$itemNested->isEmpty()) {
        foreach ($itemNested as $listItem) {
          if ($listItem instanceof ComplexDataInterface && !$listItem->isEmpty()) {
            $this->extractFields($listItem, $fieldsNested);
          }
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function extractField(TypedDataInterface $data, FieldInterface $field) {
    $values = $this->extractFieldValues($data);

    foreach ($values as $i => $value) {
      $field->addValue($value);
    }
    $field->setOriginalType($data->getDataDefinition()->getDataType());
  }

  /**
   * {@inheritdoc}
   */
  public function extractFieldValues(TypedDataInterface $data) {
    if ($data->getDataDefinition()->isList()) {
      $values = array();
      foreach ($data as $piece) {
        $values[] = $this->extractFieldValues($piece);
      }
      return $values ? call_user_func_array('array_merge', $values) : array();
    }

    $value = $data->getValue();
    $definition = $data->getDataDefinition();
    if ($definition instanceof ComplexDataDefinitionInterface) {
      $property = $definition->getMainPropertyName();
      if (isset($value[$property])) {
        return array($value[$property]);
      }
    }
    elseif (is_array($value)) {
      return array_values($value);
    }
    return array($value);
  }

  /**
   * {@inheritdoc}
   */
  public function getNestedProperties(ComplexDataDefinitionInterface $property) {
    $nestedProperties = $property->getPropertyDefinitions();
    if ($property instanceof EntityDataDefinitionInterface) {
      $bundles = $this->entityBundleInfo
        ->getBundleInfo($property->getEntityTypeId());
      foreach ($bundles as $bundle => $bundleLabel) {
        $bundleProperties = $this->entityFieldManager
          ->getFieldDefinitions($property->getEntityTypeId(), $bundle);
        $nestedProperties += $bundleProperties;
      }
    }
    return $nestedProperties;
  }

  /**
   * {@inheritdoc}
   */
  public function retrieveNestedProperty(array $properties, $propertyPath) {
    list($key, $nestedPath) = Utility::splitPropertyPath($propertyPath, FALSE);
    if (!isset($properties[$key])) {
      return NULL;
    }

    $property = $this->getInnerProperty($properties[$key]);
    if (!isset($nestedPath)) {
      return $property;
    }

    if (!$property instanceof ComplexDataDefinitionInterface) {
      return NULL;
    }

    return $this->retrieveNestedProperty($this->getNestedProperties($property), $nestedPath);
  }

  /**
   * {@inheritdoc}
   */
  public function getInnerProperty(DataDefinitionInterface $property) {
    while ($property instanceof ListDataDefinitionInterface) {
      $property = $property->getItemDefinition();
    }
    while ($property instanceof DataReferenceDefinitionInterface) {
      $property = $property->getTargetDefinition();
    }
    return $property;
  }

  /**
   * {@inheritdoc}
   */
  public function isFieldIdReserved($fieldId) {
    return substr($fieldId, 0, 11) == 'search_api_';
  }

  /**
   * {@inheritdoc}
   */
  public function createItem(IndexInterface $index, $id, DatasourceInterface $datasource = NULL) {
    return new Item($index, $id, $datasource);
  }

  /**
   * {@inheritdoc}
   */
  public function createItemFromObject(IndexInterface $index, ComplexDataInterface $originalObject, $id = NULL, DatasourceInterface $datasource = NULL) {
    if (!isset($id)) {
      if (!isset($datasource)) {
        throw new \InvalidArgumentException('Need either an item ID or the datasource to create a search item from an object.');
      }
      $id = Utility::createCombinedId($datasource->getPluginId(), $datasource->getItemId($originalObject));
    }
    $item = $this->createItem($index, $id, $datasource);
    $item->setOriginalObject($originalObject);
    return $item;
  }

  /**
   * {@inheritdoc}
   */
  public function createField(IndexInterface $index, $fieldIdentifier, $fieldInfo = array()) {
    $field = new Field($index, $fieldIdentifier);

    foreach ($fieldInfo as $key => $value) {
      $method = 'set' . Container::camelize($key);
      if (method_exists($field, $method)) {
        $field->$method($value);
      }
    }

    return $field;
  }

  /**
   * {@inheritdoc}
   */
  public function createFieldFromProperty(IndexInterface $index, DataDefinitionInterface $property, $datasourceId, $propertyPath, $fieldId = NULL, $type = NULL) {
    if (!isset($fieldId)) {
      $fieldId = $this->getNewFieldId($index, $propertyPath);
    }

    if (!isset($type)) {
      $typeMapping = $this->dataTypeHelper->getFieldTypeMapping();
      $propertyType = $property->getDataType();
      if (isset($typeMapping[$propertyType])) {
        $type = $typeMapping[$propertyType];
      }
      else {
        $propertyName = $property->getLabel();
        throw new SearchApiException("No default data type mapping could be found for property '$propertyName' ($propertyPath) of type '$propertyType'.");
      }
    }

    $fieldInfo = array(
      'label' => $property->getLabel(),
      'datasource_id' => $datasourceId,
      'property_path' => $propertyPath,
      'type' => $type,
    );
    if ($property instanceof ConfigurablePropertyInterface) {
      $fieldInfo['configuration'] = $property->defaultConfiguration();
    }
    return $this->createField($index, $fieldId, $fieldInfo);
  }

  /**
   * {@inheritdoc}
   */
  public function getNewFieldId(IndexInterface $index, $propertyPath) {
    list(, $suggestedId) = Utility::splitPropertyPath($propertyPath);

    // Avoid clashes with reserved IDs by removing the reserved "search_api_"
    // from our suggested ID.
    $suggestedId = str_replace('search_api_', '', $suggestedId);

    $fieldId = $suggestedId;
    $i = 0;
    while ($index->getField($fieldId)) {
      $fieldId = $suggestedId . '_' . ++$i;
    }

    while ($this->isFieldIdReserved($fieldId)) {
      $fieldId = '_' . $fieldId;
    }

    return $fieldId;
  }

}
