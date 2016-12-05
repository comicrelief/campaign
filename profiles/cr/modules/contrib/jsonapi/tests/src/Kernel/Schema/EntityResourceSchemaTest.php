<?php

namespace Drupal\Tests\jsonapi\Kernel\Schema;

use Drupal\KernelTests\KernelTestBase;
use Drupal\simpletest\UserCreationTrait;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class EntityResourceSchemaTest.
 *
 * @package Drupal\Tests\jsonapi\Kernel
 *
 * @coversDefaultClass \Drupal\jsonapi\Controller\SchemaController
 *
 * @group jsonapi
 */
class EntityResourceSchemaTest extends KernelTestBase {

  use UserCreationTrait;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'jsonapi',
    'entity_test',
    'user',
    'system',
    'node',
    'field',
    'text',
    'serialization',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->installConfig('jsonapi');

    $this->installEntitySchema('user');
    $this->installSchema('system', 'sequences');

    $account = $this->createUser(['access content']);
    \Drupal::currentUser()->setAccount($account);
  }

  public function testEntitySchema() {
    /** @var \Symfony\Component\HttpKernel\HttpKernelInterface $kernel */
    $kernel = \Drupal::service('http_kernel');

    $result = $kernel->handle(Request::create('/schema/entity_test_base_field_display/entity_test_base_field_display'));
    $this->assertEquals(200, $result->getStatusCode());

    $data = json_decode($result->getContent(), TRUE);
    $this->assertEquals('object', $data['type']);
    $this->assertEquals('array', $data['properties']['data']['type']);
    $this->assertEquals('object', $data['properties']['data']['items']['properties']['attributes']['type']);

    $attributes_schema = $data['properties']['data']['items']['properties']['attributes'];
    $relationships_schema = $data['properties']['data']['items']['properties']['relationships'];

    $this->assertEntityField($attributes_schema, 'id', 'ID', 'integer', NULL);
    $this->assertEntityField($attributes_schema, 'uuid', 'UUID', 'string', NULL);
    $this->assertEntityField($attributes_schema, 'langcode', 'Language', 'string', NULL);
    $this->assertEntityField($attributes_schema, 'type', NULL, 'string', NULL);
    $this->assertEntityField($attributes_schema, 'name', 'Name', 'string', NULL);
    $this->assertEntityField($attributes_schema, 'created', 'Authored on', 'integer', NULL);
    $this->assertEntityField($attributes_schema, 'test_no_display', NULL, 'object', ['value']);
    $this->assertEntityField($attributes_schema, 'test_display_multiple', NULL, 'array', NULL);
    $this->assertEntityField($relationships_schema, 'user_id', 'User ID', 'object', NULL);
  }

  protected function assertEntityField($attributes_schema, $name, $title, $type, $required_properties) {
    if ($title) {
      $this->assertEquals($title, $attributes_schema['properties'][$name]['title']);
    }
    $this->assertEquals($type, $attributes_schema['properties'][$name]['type']);

    if ($type == 'object' && isset($required_properties)) {
      $this->assertEquals($required_properties, $attributes_schema['properties'][$name]['required']);
    }
  }

}
