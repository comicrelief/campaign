<?php

namespace Drupal\Tests\jsonapi\Kernel\Resource;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Field\EntityReferenceFieldItemListInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\jsonapi\Configuration\ResourceConfigInterface;
use Drupal\jsonapi\EntityCollectionInterface;
use Drupal\jsonapi\Resource\DocumentWrapper;
use Drupal\jsonapi\Resource\EntityResource;
use Drupal\jsonapi\Routing\Param\Filter;
use Drupal\jsonapi\Routing\Param\Sort;
use Drupal\jsonapi\Routing\Param\OffsetPage;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use Drupal\Tests\jsonapi\Kernel\JsonapiKernelTestBase;
use Drupal\user\Entity\Role;
use Drupal\user\Entity\User;
use Drupal\user\RoleInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Route;

/**
 * Class EntityResourceTest.
 *
 * @package Drupal\Tests\jsonapi\Kernel\Resource
 *
 * @coversDefaultClass \Drupal\jsonapi\Resource\EntityResource
 *
 * @group jsonapi
 */
class EntityResourceTest extends JsonapiKernelTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'node',
    'field',
    'jsonapi',
    'rest',
    'serialization',
    'system',
    'user',
  ];

  /**
   * The user.
   *
   * @var \Drupal\user\Entity\User
   */
  protected $user;

  /**
   * The node.
   *
   * @var \Drupal\node\Entity\Node
   */
  protected $node;

  /**
   * The other node.
   *
   * @var \Drupal\node\Entity\Node
   */
  protected $node2;

  /**
   * A fake request.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    // Add the entity schemas.
    $this->installEntitySchema('node');
    $this->installEntitySchema('user');
    // Add the additional table schemas.
    $this->installSchema('system', ['sequences']);
    $this->installSchema('node', ['node_access']);
    $this->installSchema('user', ['users_data']);
    NodeType::create([
      'type' => 'lorem',
    ])->save();
    $type = NodeType::create([
      'type' => 'article',
    ]);
    $type->save();
    $this->user = User::create([
      'name' => 'user1',
      'mail' => 'user@localhost',
      'status' => 1,
    ]);
    $this->createEntityReferenceField('node', 'article', 'field_relationships', 'Relationship', 'node', 'default', ['target_bundles' => ['article']], FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED);
    $this->user->save();
    $this->node = Node::create([
      'title' => 'dummy_title',
      'type' => 'article',
      'uid' => $this->user->id(),
    ]);
    $this->node->save();

    $this->node2 = Node::create([
      'type' => 'article',
      'title' => 'Another test node',
      'uid' => $this->user->id(),
    ]);
    $this->node2->save();

    // Give anonymous users permission to view user profiles, so that we can
    // verify the cache tags of cached versions of user profile pages.
    Role::create([
      'id' => RoleInterface::ANONYMOUS_ID,
      'permissions' => [
        'access user profiles',
        'access content',
      ],
    ])->save();

    $this->request = $this->prophesize(Request::class);
  }


  /**
   * @covers ::getIndividual
   */
  public function testGetIndividual() {
    $entity_resource = $this->buildEntityResource('node', 'article', 'id');
    $response = $entity_resource->getIndividual($this->node, $this->request->reveal());
    $this->assertInstanceOf(DocumentWrapper::class, $response->getResponseData());
    $this->assertEquals(1, $response->getResponseData()->getData()->id());
  }

  /**
   * @covers ::getIndividual
   * @expectedException \Drupal\jsonapi\Error\SerializableHttpException
   */
  public function testGetIndividualDenied() {
    $role = Role::load(RoleInterface::ANONYMOUS_ID);
    $role->revokePermission('access content');
    $role->save();
    $entity_resource = $this->buildEntityResource('node', 'article', 'id');
    $entity_resource->getIndividual($this->node, $this->request->reveal());
  }

  /**
   * @covers ::getCollection
   */
  public function testGetCollection() {
    // Fake the request.
    $request = $this->prophesize(Request::class);
    $params = $this->prophesize(ParameterBag::class);
    $params->get('_route_params')->willReturn(['_json_api_params' => []]);
    $request->attributes = $params->reveal();
    $params->get('_json_api_params')->willReturn([]);

    // Get the response.
    $entity_resource = $this->buildEntityResource('node', 'article', 'id');
    $response = $entity_resource->getCollection($request->reveal());

    // Assertions.
    $this->assertInstanceOf(DocumentWrapper::class, $response->getResponseData());
    $this->assertInstanceOf(EntityCollectionInterface::class, $response->getResponseData()->getData());
    $this->assertEquals(1, $response->getResponseData()->getData()->getIterator()->current()->id());
    $this->assertEquals(['node:1', 'node:2', 'node_list'], $response->getCacheableMetadata()->getCacheTags());
  }

  /**
   * @covers ::getCollection
   */
  public function testGetFilteredCollection() {
    // Fake the request.
    $request = $this->prophesize(Request::class);
    $params = $this->prophesize(ParameterBag::class);
    $field_manager = $this->container->get('entity_field.manager');
    $filter = new Filter(['type' => ['value' => 'article']], 'node_type', $field_manager);
    $params->get('_route_params')->willReturn([
      '_json_api_params' => [
        'filter' => $filter,
      ],
    ]);
    $params->get('_json_api_params')->willReturn([
      'filter' => $filter,
    ]);
    $request->attributes = $params->reveal();

    // Get the entity resource.
    $current_context = $this->container->get('jsonapi.current_context');
    $route = $this->prophesize(Route::class);
    $route->getRequirement('_entity_type')->willReturn('node');
    $route->getRequirement('_bundle')->willReturn('article');
    $current_context->setCurrentRoute($route->reveal());
    $entity_resource = new EntityResource(
      $this->container->get('jsonapi.resource.manager')->get('node_type', 'node_type'),
      $this->container->get('entity_type.manager'),
      $this->container->get('jsonapi.query_builder'),
      $field_manager,
      $current_context,
      $this->container->get('plugin.manager.field.field_type')
    );

    // Get the response.
    $response = $entity_resource->getCollection($request->reveal());

    // Assertions.
    $this->assertInstanceOf(DocumentWrapper::class, $response->getResponseData());
    $this->assertInstanceOf(EntityCollectionInterface::class, $response->getResponseData()->getData());
    $this->assertCount(1, $response->getResponseData()->getData());
    $this->assertEquals(['config:node_type_list'], $response->getCacheableMetadata()->getCacheTags());
  }

  /**
   * @covers ::getCollection
   */
  public function testGetSortedCollection() {
    // Fake the request.
    $request = $this->prophesize(Request::class);
    $params = $this->prophesize(ParameterBag::class);
    $field_manager = $this->container->get('entity_field.manager');
    $sort = new Sort('-type');
    $params->get('_route_params')->willReturn([
      '_json_api_params' => [
        'sort' => $sort,
      ],
    ]);
    $params->get('_json_api_params')->willReturn([
      'sort' => $sort,
    ]);
    $request->attributes = $params->reveal();

    // Get the entity resource.
    $current_context = $this->container->get('jsonapi.current_context');
    $route = $this->prophesize(Route::class);
    $route->getRequirement('_entity_type')->willReturn('node');
    $route->getRequirement('_bundle')->willReturn('article');
    $current_context->setCurrentRoute($route->reveal());
    $entity_resource = new EntityResource(
      $this->container->get('jsonapi.resource.manager')->get('node_type', 'node_type'),
      $this->container->get('entity_type.manager'),
      $this->container->get('jsonapi.query_builder'),
      $field_manager,
      $current_context,
      $this->container->get('plugin.manager.field.field_type')
    );

    // Get the response.
    $response = $entity_resource->getCollection($request->reveal());

    // Assertions.
    $this->assertInstanceOf(DocumentWrapper::class, $response->getResponseData());
    $this->assertInstanceOf(EntityCollectionInterface::class, $response->getResponseData()->getData());
    $this->assertCount(2, $response->getResponseData()->getData());
    $this->assertEquals($response->getResponseData()->getData()->toArray()[0]->id(), 'lorem');
    $this->assertEquals(['config:node_type_list'], $response->getCacheableMetadata()->getCacheTags());
  }

  /**
   * @covers ::getCollection
   */
  public function testGetPagedCollection() {
    // Fake the request.
    $request = $this->prophesize(Request::class);
    $params = $this->prophesize(ParameterBag::class);
    $field_manager = $this->container->get('entity_field.manager');
    $pager = new OffsetPage(['offset' => 1, 'size' => 1]);
    $params->get('_route_params')->willReturn([
      '_json_api_params' => [
        'page' => $pager,
      ],
    ]);
    $params->get('_json_api_params')->willReturn([
      'page' => $pager,
    ]);
    $request->attributes = $params->reveal();

    // Get the entity resource.
    $current_context = $this->container->get('jsonapi.current_context');
    $route = $this->prophesize(Route::class);
    $route->getRequirement('_entity_type')->willReturn('node');
    $route->getRequirement('_bundle')->willReturn('article');
    $current_context->setCurrentRoute($route->reveal());
    $entity_resource = new EntityResource(
      $this->container->get('jsonapi.resource.manager')->get('node', 'article'),
      $this->container->get('entity_type.manager'),
      $this->container->get('jsonapi.query_builder'),
      $field_manager,
      $current_context,
      $this->container->get('plugin.manager.field.field_type')
    );

    // Get the response.
    $response = $entity_resource->getCollection($request->reveal());

    // Assertions.
    $this->assertInstanceOf(DocumentWrapper::class, $response->getResponseData());
    $this->assertInstanceOf(EntityCollectionInterface::class, $response->getResponseData()->getData());
    $data = $response->getResponseData()->getData();
    $this->assertCount(1, $data);
    $this->assertEquals(2, $data->toArray()[0]->id());
    $this->assertEquals(['node:2', 'node_list'], $response->getCacheableMetadata()->getCacheTags());
  }

  /**
   * @covers ::getCollection
   */
  public function testGetEmptyCollection() {
    // Fake the request.
    $request = $this->prophesize(Request::class);
    $params = $this->prophesize(ParameterBag::class);
    $filter = new Filter(
      ['uuid' => ['value' => 'invalid']],
      'node',
      $this->container->get('entity_field.manager')
    );
    $params->get('_route_params')->willReturn([
      '_json_api_params' => [
        'filter' => $filter,
      ],
    ]);
    $params->get('_json_api_params')->willReturn([
      'filter' => $filter,
    ]);
    $request->attributes = $params->reveal();

    // Get the response.
    $entity_resource = $this->buildEntityResource('node', 'article', 'id');
    $response = $entity_resource->getCollection($request->reveal());

    // Assertions.
    $this->assertInstanceOf(DocumentWrapper::class, $response->getResponseData());
    $this->assertInstanceOf(EntityCollectionInterface::class, $response->getResponseData()->getData());
    $this->assertEquals(0, $response->getResponseData()->getData()->count());
    $this->assertEquals(['node_list'], $response->getCacheableMetadata()->getCacheTags());
  }

  /**
   * @covers ::getRelated
   */
  public function testGetRelated() {
    // to-one relationship.
    $entity_resource = $this->buildEntityResource('node', 'article', 'id');
    $response = $entity_resource->getRelated($this->node, 'uid', $this->request->reveal());
    $this->assertInstanceOf(DocumentWrapper::class, $response->getResponseData());
    $this->assertInstanceOf(User::class, $response->getResponseData()
      ->getData());
    $this->assertEquals(1, $response->getResponseData()->getData()->id());

    // to-many relationship.
    $response = $entity_resource->getRelated($this->user, 'roles', $this->request->reveal());
    $this->assertInstanceOf(DocumentWrapper::class, $response
      ->getResponseData());
    $this->assertInstanceOf(EntityCollectionInterface::class, $response
      ->getResponseData()
      ->getData());
    $this->assertEquals(['config:user_role_list'], $response
      ->getCacheableMetadata()
      ->getCacheTags());
  }

  /**
   * @covers ::getRelationship
   */
  public function testGetRelationship() {
    // to-one relationship.
    $entity_resource = $this->buildEntityResource('node', 'article', 'id');
    $response = $entity_resource->getRelationship($this->node, 'uid', $this->request->reveal());
    $this->assertInstanceOf(DocumentWrapper::class, $response->getResponseData());
    $this->assertInstanceOf(
      EntityReferenceFieldItemListInterface::class,
      $response->getResponseData()->getData()
    );
    $this->assertEquals(1, $response
      ->getResponseData()
      ->getData()
      ->getEntity()
      ->id()
    );
    $this->assertEquals('node', $response
      ->getResponseData()
      ->getData()
      ->getEntity()
      ->getEntityTypeId()
    );
  }

  /**
   * @covers ::createIndividual
   */
  public function testCreateIndividual() {
    $node = Node::create([
      'type' => 'article',
      'title' => 'Lorem ipsum',
    ]);
    Role::load(Role::ANONYMOUS_ID)
      ->grantPermission('create article content')
      ->save();
    $entity_resource = $this->buildEntityResource('node', 'article', 'id');
    $response = $entity_resource->createIndividual($node, $this->request->reveal());
    // As a side effect, the node will also be saved.
    $this->assertNotEmpty($node->id());
    $this->assertInstanceOf(DocumentWrapper::class, $response->getResponseData());
    $this->assertEquals(3, $response->getResponseData()->getData()->id());
    $this->assertEquals(201, $response->getStatusCode());
  }

  /**
   * @covers ::createIndividual
   */
  public function testCreateIndividualWithMissingRequiredData() {
    $node = Node::create([
      'type' => 'article',
      // No title specified, even if its required.
    ]);
    Role::load(Role::ANONYMOUS_ID)
      ->grantPermission('create article content')
      ->save();
    $this->setExpectedException(HttpException::class, 'Unprocessable Entity: validation failed.
title: This value should not be null.');
    $entity_resource = $this->buildEntityResource('node', 'article', 'id');
    $entity_resource->createIndividual($node, $this->request->reveal());
  }

  /**
   * @covers ::createIndividual
   */
  public function testCreateIndividualConfig() {
    $node_type = NodeType::create([
      'type' => 'test',
      'name' => 'Test Type',
      'description' => 'Lorem ipsum',
    ]);
    Role::load(Role::ANONYMOUS_ID)
      ->grantPermission('administer content types')
      ->save();
    $entity_resource = $this->buildEntityResource('node', 'article', 'id');
    $response = $entity_resource->createIndividual($node_type, $this->request->reveal());
    // As a side effect, the node type will also be saved.
    $this->assertNotEmpty($node_type->id());
    $this->assertInstanceOf(DocumentWrapper::class, $response->getResponseData());
    $this->assertEquals('test', $response->getResponseData()->getData()->id());
    $this->assertEquals(201, $response->getStatusCode());
  }

  /**
   * @covers ::patchIndividual
   * @dataProvider patchIndividualProvider
   */
  public function testPatchIndividual($values) {
    $parsed_node = Node::create($values);
    Role::load(Role::ANONYMOUS_ID)
      ->grantPermission('edit any article content')
      ->save();
    $request = $this->prophesize(Request::class);
    $request->getContent()->willReturn('{"data":{"type":"article","id":1,"attributes":{"title": "","field_relationships":""}}}');

    // Create a new EntityResource that uses uuid.
    $entity_resource = $this->buildEntityResource('node', 'article', 'id');
    $response = $entity_resource->patchIndividual($this->node, $parsed_node, $request->reveal());

    // As a side effect, the node will also be saved.
    $this->assertInstanceOf(DocumentWrapper::class, $response->getResponseData());
    $updated_node = $response->getResponseData()->getData();
    $this->assertInstanceOf(Node::class, $updated_node);
    $this->assertSame($values['title'], $this->node->getTitle());
    $this->assertSame($values['field_relationships'], $this->node->get('field_relationships')->getValue());
    $this->assertEquals(200, $response->getStatusCode());
  }

  /**
   * @covers ::patchIndividual
   * @dataProvider patchIndividualProvider
   */
  public function testPatchIndividualUuid($values) {
    $parsed_node = Node::create($values);
    Role::load(Role::ANONYMOUS_ID)
      ->grantPermission('edit any article content')
      ->save();
    $uuid = $this->node->uuid();
    $request = $this->prophesize(Request::class);
    $request->getContent()->willReturn(sprintf(
      '{"data":{"type":"article","id":"%s","attributes":{"title": "","field_relationships":""}}}',
      $uuid
    ));

    $entity_resource = $this->buildEntityResource('node', 'article', 'uuid');
    $response = $entity_resource->patchIndividual($this->node, $parsed_node, $request->reveal());

    // As a side effect, the node will also be saved.
    $this->assertInstanceOf(DocumentWrapper::class, $response->getResponseData());
    $updated_node = $response->getResponseData()->getData();
    $this->assertInstanceOf(Node::class, $updated_node);
    $this->assertSame($values['title'], $this->node->getTitle());
    $this->assertSame($values['field_relationships'], $this->node->get('field_relationships')->getValue());
    $this->assertEquals(200, $response->getStatusCode());
  }

  /**
   * Provides data for the testPatchIndividual.
   *
   * @return array
   *   The input data for the test function.
   */
  public function patchIndividualProvider() {
    return [
      [
        [
          'type' => 'article',
          'title' => 'PATCHED',
          'field_relationships' => [['target_id' => 1]],
        ],
      ],
    ];
  }

  /**
   * @covers ::patchIndividual
   * @dataProvider patchIndividualConfigProvider
   */
  public function testPatchIndividualConfig($values) {
    // List of fields to be ignored.
    $ignored_fields = ['uuid', 'entityTypeId', 'type'];
    $node_type = NodeType::create([
      'type' => 'test',
      'name' => 'Test Type',
      'description' => '',
    ]);
    $node_type->save();

    $parsed_node_type = NodeType::create($values);
    Role::load(Role::ANONYMOUS_ID)
      ->grantPermission('administer content types')
      ->save();
    Role::load(Role::ANONYMOUS_ID)
      ->grantPermission('edit any article content')
      ->save();
    $request = $this->prophesize(Request::class);
    $payload = Json::encode([
      'data' => [
        'type' => 'node_type',
        'id' => 'test',
        'attributes' => $values,
      ],
    ]);
    $request->getContent()->willReturn($payload);

    $entity_resource = $this->buildEntityResource('node', 'article', 'id');
    $response = $entity_resource->patchIndividual($node_type, $parsed_node_type, $request->reveal());

    // As a side effect, the node will also be saved.
    $this->assertInstanceOf(DocumentWrapper::class, $response->getResponseData());
    $updated_node_type = $response->getResponseData()->getData();
    $this->assertInstanceOf(NodeType::class, $updated_node_type);
    // If the field is ignored then we should not see a difference.
    foreach ($values as $field_name => $value) {
      in_array($field_name, $ignored_fields) ?
        $this->assertNotSame($value, $node_type->get($field_name)) :
        $this->assertSame($value, $node_type->get($field_name));
    }
    $this->assertEquals(200, $response->getStatusCode());
  }

  /**
   * Provides data for the testPatchIndividualConfig.
   *
   * @return array
   *   The input data for the test function.
   */
  public function patchIndividualConfigProvider() {
    return [
      [['description' => 'PATCHED', 'status' => FALSE]],
      [[]],
    ];
  }

  /**
   * @covers ::patchIndividual
   * @dataProvider patchIndividualConfigFailedProvider
   * @expectedException \Drupal\Core\Config\ConfigException
   */
  public function testPatchIndividualFailedConfig($values) {
    $this->testPatchIndividualConfig($values);
  }

  /**
   * Provides data for the testPatchIndividualFailedConfig.
   *
   * @return array
   *   The input data for the test function.
   */
  public function patchIndividualConfigFailedProvider() {
    return [
      [['uuid' => 'PATCHED']],
      [['type' => 'article', 'status' => FALSE]],
    ];
  }

  /**
   * @covers ::deleteIndividual
   */
  public function testDeleteIndividual() {
    $node = Node::create([
      'type' => 'article',
      'title' => 'Lorem ipsum',
    ]);
    $nid = $node->id();
    $node->save();
    Role::load(Role::ANONYMOUS_ID)
      ->grantPermission('delete own article content')
      ->save();
    $entity_resource = $this->buildEntityResource('node', 'article', 'id');
    $response = $entity_resource->deleteIndividual($node, $this->request->reveal());
    // As a side effect, the node will also be deleted.
    $count = $this->container->get('entity_type.manager')
      ->getStorage('node')
      ->getQuery()
      ->condition('nid', $nid)
      ->count()
      ->execute();
    $this->assertEquals(0, $count);
    $this->assertNull($response->getResponseData());
    $this->assertEquals(204, $response->getStatusCode());
  }

  /**
   * @covers ::deleteIndividual
   */
  public function testDeleteIndividualConfig() {
    $node_type = NodeType::create([
      'type' => 'test',
      'name' => 'Test Type',
      'description' => 'Lorem ipsum',
    ]);
    $id = $node_type->id();
    $node_type->save();
    Role::load(Role::ANONYMOUS_ID)
      ->grantPermission('administer content types')
      ->save();
    $entity_resource = $this->buildEntityResource('node', 'article', 'id');
    $response = $entity_resource->deleteIndividual($node_type, $this->request->reveal());
    // As a side effect, the node will also be deleted.
    $count = $this->container->get('entity_type.manager')
      ->getStorage('node_type')
      ->getQuery()
      ->condition('type', $id)
      ->count()
      ->execute();
    $this->assertEquals(0, $count);
    $this->assertNull($response->getResponseData());
    $this->assertEquals(204, $response->getStatusCode());
  }

  /**
   * @covers ::createRelationship
   */
  public function testCreateRelationship() {
    $parsed_field_list = $this->container
      ->get('plugin.manager.field.field_type')
      ->createFieldItemList($this->node, 'field_relationships', [
        ['target_id' => $this->node->id()],
      ]);
    Role::load(Role::ANONYMOUS_ID)
      ->grantPermission('edit any article content')
      ->save();

    $entity_resource = $this->buildEntityResource('node', 'article', 'id');
    $response = $entity_resource->createRelationship($this->node, 'field_relationships', $parsed_field_list, $this->request->reveal());

    // As a side effect, the node will also be saved.
    $this->assertNotEmpty($this->node->id());
    $this->assertInstanceOf(DocumentWrapper::class, $response->getResponseData());
    $field_list = $response->getResponseData()->getData();
    $this->assertInstanceOf(EntityReferenceFieldItemListInterface::class, $field_list);
    $this->assertSame('field_relationships', $field_list->getName());
    $this->assertEquals([['target_id' => 1]], $field_list->getValue());
    $this->assertEquals(201, $response->getStatusCode());
  }

  /**
   * @covers ::patchRelationship
   * @dataProvider patchRelationshipProvider
   */
  public function testPatchRelationship($relationships) {
    $this->node->field_relationships->appendItem(['target_id' => $this->node->id()]);
    $this->node->save();
    $parsed_field_list = $this->container
      ->get('plugin.manager.field.field_type')
      ->createFieldItemList($this->node, 'field_relationships', $relationships);
    Role::load(Role::ANONYMOUS_ID)
      ->grantPermission('edit any article content')
      ->save();

    $entity_resource = $this->buildEntityResource('node', 'article', 'id');
    $response = $entity_resource->patchRelationship($this->node, 'field_relationships', $parsed_field_list, $this->request->reveal());

    // As a side effect, the node will also be saved.
    $this->assertNotEmpty($this->node->id());
    $this->assertInstanceOf(DocumentWrapper::class, $response->getResponseData());
    $field_list = $response->getResponseData()->getData();
    $this->assertInstanceOf(EntityReferenceFieldItemListInterface::class, $field_list);
    $this->assertSame('field_relationships', $field_list->getName());
    $this->assertEquals($relationships, $field_list->getValue());
    $this->assertEquals(200, $response->getStatusCode());
  }

  /**
   * Provides data for the testPatchRelationship.
   *
   * @return array
   *   The input data for the test function.
   */
  public function patchRelationshipProvider() {
    return [
      // Replace relationships.
      [[['target_id' => 2], ['target_id' => 1]]],
      // Remove relationships.
      [[]],
    ];
  }

  /**
   * @covers ::deleteRelationship
   * @dataProvider deleteRelationshipProvider
   */
  public function testDeleteRelationship($deleted_rels, $kept_rels) {
    $this->node->field_relationships->appendItem(['target_id' => $this->node->id()]);
    $this->node->field_relationships->appendItem(['target_id' => $this->node2->id()]);
    $this->node->save();
    $parsed_field_list = $this->container
      ->get('plugin.manager.field.field_type')
      ->createFieldItemList($this->node, 'field_relationships', $deleted_rels);
    Role::load(Role::ANONYMOUS_ID)
      ->grantPermission('edit any article content')
      ->save();

    $entity_resource = $this->buildEntityResource('node', 'article', 'id');
    $response = $entity_resource->deleteRelationship($this->node, 'field_relationships', $parsed_field_list, $this->request->reveal());

    // As a side effect, the node will also be saved.
    $this->assertInstanceOf(DocumentWrapper::class, $response->getResponseData());
    $field_list = $response->getResponseData()->getData();
    $this->assertInstanceOf(EntityReferenceFieldItemListInterface::class, $field_list);
    $this->assertSame('field_relationships', $field_list->getName());
    $this->assertEquals($kept_rels, $field_list->getValue());
    $this->assertEquals(201, $response->getStatusCode());
  }

  /**
   * Provides data for the testDeleteRelationship.
   *
   * @return array
   *   The input data for the test function.
   */
  public function deleteRelationshipProvider() {
    return [
      // Remove one relationship.
      [[['target_id' => 1]], [['target_id' => 2]]],
      // Remove all relationships.
      [[['target_id' => 2], ['target_id' => 1]], []],
      // Remove no relationship.
      [[], [['target_id' => 1], ['target_id' => 2]]],
    ];
  }

  /**
   * Instantiates a test EntityResource.
   *
   * @param string $entity_type_id
   *   The entity type ID.
   * @param string $bundle_id
   *   The bundle ID.
   * @param string $id_field
   *   The field used to ID the entity.
   *
   * @return \Drupal\jsonapi\Resource\EntityResourceInterface
   *   The resource.
   */
  protected function buildEntityResource($entity_type_id, $bundle_id, $id_field) {
    $current_context = $this->container->get('jsonapi.current_context');
    $route = $this->prophesize(Route::class);
    $route->getRequirement('_entity_type')->willReturn($entity_type_id);
    $route->getRequirement('_bundle')->willReturn($bundle_id);
    $current_context->setCurrentRoute($route->reveal());
    $resource_config = $this->prophesize(ResourceConfigInterface::class);
    $resource_config->getEntityTypeId()->willReturn($entity_type_id);
    $resource_config->getBundleId()->willReturn($bundle_id);
    $resource_config->getIdKey()->willReturn($id_field);
    return new EntityResource(
      $resource_config->reveal(),
      $this->container->get('entity_type.manager'),
      $this->container->get('jsonapi.query_builder'),
      $this->container->get('entity_field.manager'),
      $current_context,
      $this->container->get('plugin.manager.field.field_type')
    );
  }
}
