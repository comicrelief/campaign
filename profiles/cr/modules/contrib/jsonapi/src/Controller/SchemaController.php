<?php

namespace Drupal\jsonapi\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\TypedData\FieldItemDataDefinition;
use Drupal\Core\TypedData\ComplexDataDefinitionInterface;
use Drupal\Core\TypedData\DataDefinitionInterface;
use Drupal\Core\TypedData\ListDataDefinitionInterface;
use Drupal\Core\TypedData\TypedDataManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Provides schema for the various exposed resources.
 */
class SchemaController extends ControllerBase {

  /**
   * The typed data manager.
   *
   * @var \Drupal\Core\TypedData\TypedDataManagerInterface
   */
  protected $typedDataManager;

  /**
   * SchemaController constructor.
   *
   * @param \Drupal\Core\TypedData\TypedDataManagerInterface $typedDataManager
   *   The typed data manager.
   */
  public function __construct(TypedDataManagerInterface $typedDataManager) {
    $this->typedDataManager = $typedDataManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('typed_data_manager'));
  }

  /**
   * Returns schema from a given type data definition.
   *
   * @param \Drupal\Core\TypedData\DataDefinitionInterface $data_definition
   *   The type data.
   *
   * @return array
   *   The schema.
   */
  protected function buildSchemaFromDataDefinition(DataDefinitionInterface $data_definition) {
    $schema = [];

    if ($label = $data_definition->getLabel()) {
      $schema['title'] = $label;
    }
    if ($description = $data_definition->getDescription()) {
      $schema['description'] = $description;
    }

    if ($data_definition instanceof ListDataDefinitionInterface) {
      // If the schema is for a field, then check the cardinality.
      if (
        $data_definition instanceof FieldDefinitionInterface &&
        $data_definition->getFieldStorageDefinition()->getCardinality() == 1
      ) {
        // Unwrap the array if this is a single cardinality field.
        $schema = $this->buildSchemaFromDataDefinition($data_definition->getItemDefinition());
        $schema['title'] = $label;
      }
      else {
        $schema['type'] = 'array';
        $schema['items'] = $this->buildSchemaFromDataDefinition($data_definition->getItemDefinition());
      }
    }
    elseif ($data_definition instanceof ComplexDataDefinitionInterface) {
      $schema['type'] = 'object';
      $schema['properties'] = array_filter(array_map(function (DataDefinitionInterface $sub_data_definition) {
        if (!$sub_data_definition->isComputed()) {
          return $this->buildSchemaFromDataDefinition($sub_data_definition);
        }
        return NULL;
      }, $data_definition->getPropertyDefinitions()));

      $schema['required'] = array_keys(array_filter($data_definition->getPropertyDefinitions(), function (DataDefinitionInterface $definition) {
        return $definition->isRequired();
      }));

      if ($data_definition instanceof FieldItemDataDefinition && count($schema['properties']) == 1) {
        // If this is a field item with a single property, then use that property.
        $schema = reset($schema['properties']);
      }
    }
    else {
      $schema['type'] = $this->convertTypeDataToJsonType($data_definition->getDataType());
    }

    return $schema;
  }

  /**
   * Converts a type data type to JSON.
   *
   * @param string $type
   *   The type data type.
   *
   * @return string
   *   Returns the JSON data type.
   */
  protected function convertTypeDataToJsonType($type) {
    $json_type = '';
    switch ($type) {
      case 'string':
      case 'filter_format':
        $json_type = 'string';
        break;
      case 'integer':
      case 'timestamp':
        $json_type = 'integer';
        break;
      case 'boolean':
        $json_type = 'boolean';
        break;
      case 'float':
        $json_type = 'number';
        break;
    }

    return $json_type;
  }

  /**
   * Provides schema for the individual entity resource.
   *
   * @param string $typed_data_id
   *   The type data ID.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   A JSON response containing the JSON scheme.
   */
  public function entitySchema($typed_data_id) {
    $schema = [];
    $schema['type'] = 'object';
    $schema['title'] = $this->t('Document Root');
    $schema['properties'] = [
      'data' => $this->buildResourceObjectSchema($typed_data_id),
    ];

    return new JsonResponse($schema);
  }

  /**
   * Provides schema for the collection entity resource.
   *
   * @param string $typed_data_id
   *   The type data ID.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   A JSON response containing the JSON scheme.
   */
  public function entityCollectionSchema($typed_data_id) {
    $schema = [];
    $schema['type'] = 'object';
    $schema['title'] = $this->t('Document Root');
    $schema['properties'] = [
      'data' => [
        'type' => 'array',
        'items' => $this->buildResourceObjectSchema($typed_data_id),
      ],
    ];

    return new JsonResponse($schema);
  }

  /**
   * Builds the schema for the resource object.
   *
   * @param string $typed_data_id
   *   The type data ID.
   *
   * @return array
   *   The schema for the resource object as described in the JSON API spec.
   */
  protected function buildResourceObjectSchema($typed_data_id) {
    $data_definition = $this->typedDataManager->createDataDefinition($typed_data_id);

    list($attributes, $relationships) = $this->buildPropertiesSchema($data_definition);

    return [
      'type' => 'object',
      'title' => $this->t('Primary data'),
      'properties' => [
        'attributes' => [
          'type' => 'object',
          'title' => $this->t('Resource\'s data'),
          'properties' => $attributes,
        ],
        'relationships' => [
          'type' => 'object',
          'title' => $this->t('Resource\'s relationships'),
          'properties' => $relationships,
        ],
      ],
    ];
  }

  /**
   * Extract the JSON API attributes and relationships from the typed data.
   *
   * @param \Drupal\Core\TypedData\DataDefinitionInterface $data_definition
   *
   * @return array
   *   First element is for the attributes, second element for relationships.
   */
  protected function buildPropertiesSchema(DataDefinitionInterface $data_definition) {
    $property_schemas = $this->buildSchemaFromDataDefinition($data_definition);
    $attributes = [];
    $relationships = [];
    foreach ($property_schemas['properties'] as $label => $property_schema) {
      $property_definition = $data_definition->getPropertyDefinition($label);
      $is_relationship = $property_definition instanceof FieldDefinitionInterface && $property_definition->getType() == 'entity_reference';
      if ($is_relationship) {
        $relationships[$label] = $this->buildRelationship($property_schema);
      }
      else {
        $attributes[$label] = $property_schema;
      }
    }

    return array($attributes, $relationships);
  }

  /**
   * Build the schema for the resource relationship object.
   *
   * @param array $property_schema
   *   The schema for the property representing the relationship.
   *
   * @return array
   *   The schema for the relationship.
   */
  protected function buildRelationship(array $property_schema) {
    // Build the relationship schema.
    $resource_identifier = [];
    $resource_identifier['type'] = 'object';
    $resource_identifier['title'] = $this->t('Resource identifier');
    if (!empty($property_schema['title'])) {
      $resource_identifier['title'] = $property_schema['title'];
    }
    if (!empty($property_schema['description'])) {
      $resource_identifier['description'] = $property_schema['description'];
    }
    $resource_identifier['properties'] = [
      'type' => [
        'type' => 'string',
        'title' => $this->t('Resource name'),
      ],
      'id' => [
        'type' => $property_schema['type'] == 'array' ?
          $property_schema['items']['type'] :
          $property_schema['type'],
      ],
    ];

    if ($property_schema['type'] == 'array') {
      return [
        'type' => 'array',
        'title' => $this->t('Resource identifier'),
        'items' => $resource_identifier,
      ];
    }
    return $resource_identifier;
  }

}
