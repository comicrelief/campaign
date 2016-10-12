<?php

namespace Drupal\yamlform;

use Drupal\Core\Entity\ContentEntityTypeInterface;
use Drupal\Core\Entity\Sql\SqlContentEntityStorageSchema;

/**
 * Defines the form submission schema handler.
 */
class YamlFormSubmissionStorageSchema extends SqlContentEntityStorageSchema {

  /**
   * {@inheritdoc}
   */
  protected function getEntitySchema(ContentEntityTypeInterface $entity_type, $reset = FALSE) {
    $schema = parent::getEntitySchema($entity_type, $reset = FALSE);

    $schema['yamlform_submission_data'] = [
      'description' => 'Stores all submitted data for form submissions.',
      'fields' => [
        'yamlform_id' => [
          'description' => 'The form id.',
          'type' => 'varchar',
          'length' => 32,
          'not null' => TRUE,
        ],
        'sid' => [
          'description' => 'The unique identifier for this submission.',
          'type' => 'int',
          'unsigned' => TRUE,
          'not null' => TRUE,
        ],
        'name' => [
          'description' => 'The name of the element.',
          'type' => 'varchar',
          'length' => 128,
          'not null' => TRUE,
        ],
        'property' => [
          'description' => "The property of the element's value.",
          'type' => 'varchar',
          'length' => 128,
          'not null' => TRUE,
          'default' => '',
        ],
        'delta' => [
          'description' => "The delta of the element's value.",
          'type' => 'int',
          'unsigned' => TRUE,
          'not null' => TRUE,
          'default' => 0,
        ],
        'value' => [
          'description' => "The element's value.",
          'type' => 'text',
          'size' => 'medium',
          'not null' => TRUE,
        ],
      ],
      'primary key' => ['sid', 'name', 'property', 'delta'],
      'indexes' => [
        'yamlform_id' => ['yamlform_id'],
        'sid_yamlform_id' => ['sid', 'yamlform_id'],
      ],
    ];

    return $schema;
  }

}
