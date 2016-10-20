<?php

namespace Drupal\Tests\jsonapi\Kernel\Normalizer;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\jsonapi\LinkManager\LinkManagerInterface;
use Drupal\jsonapi\Normalizer\DocumentRootNormalizerInterface;
use Drupal\jsonapi\Resource\DocumentWrapper;
use Drupal\jsonapi\Configuration\ResourceConfigInterface;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use Drupal\rest\ResourceResponse;
use Drupal\taxonomy\Entity\Term;
use Drupal\taxonomy\Entity\Vocabulary;
use Drupal\Tests\jsonapi\Kernel\JsonapiKernelTestBase;
use Drupal\user\Entity\User;
use Prophecy\Argument;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Route;

/**
 * Class DocumentRootNormalizerTest.
 *
 * @package Drupal\jsonapi\Normalizer
 *
 * @coversDefaultClass \Drupal\jsonapi\Normalizer\ContentEntityNormalizer
 *
 * @group jsonapi
 */
class DocumentRootNormalizerTest extends JsonapiKernelTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'jsonapi',
    'field',
    'node',
    'rest',
    'serialization',
    'system',
    'taxonomy',
    'text',
    'user',
  ];

  /**
   * A node to normalize.
   *
   * @var \Drupal\Core\Entity\EntityInterface
   */
  protected $node;

  /**
   * A user to normalize.
   *
   * @var \Drupal\user\Entity\User
   */
  protected $user;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    // Add the entity schemas.
    $this->installEntitySchema('node');
    $this->installEntitySchema('user');
    $this->installEntitySchema('taxonomy_term');
    // Add the additional table schemas.
    $this->installSchema('system', ['sequences']);
    $this->installSchema('node', ['node_access']);
    $this->installSchema('user', ['users_data']);
    $type = NodeType::create([
      'type' => 'article',
    ]);
    $type->save();
    $this->createEntityReferenceField(
      'node',
      'article',
      'field_tags',
      'Tags',
      'taxonomy_term',
      'default',
      ['target_bundles' => ['tags']],
      FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED
    );
    $this->user = User::create([
      'name' => 'user1',
      'mail' => 'user@localhost',
    ]);
    $this->user2 = User::create([
      'name' => 'user2',
      'mail' => 'user2@localhost',
    ]);

    $this->user->save();
    $this->user2->save();

    $this->vocabulary = Vocabulary::create(['name' => 'Tags', 'vid' => 'tags']);
    $this->vocabulary->save();

    $this->term1 = Term::create([
      'name' => 'term1',
      'vid' => $this->vocabulary->id(),
    ]);
    $this->term2 = Term::create([
      'name' => 'term2',
      'vid' => $this->vocabulary->id(),
    ]);

    $this->term1->save();
    $this->term2->save();

    $this->node = Node::create([
      'title' => 'dummy_title',
      'type' => 'article',
      'uid' => 1,
    ]);

    $this->node->save();

    $link_manager = $this->prophesize(LinkManagerInterface::class);
    $link_manager
      ->getEntityLink(Argument::any(), Argument::any(), Argument::type('array'), Argument::type('string'))
      ->willReturn('dummy_entity_link');
    $link_manager
      ->getRequestLink(Argument::any())
      ->willReturn('dummy_document_link');
    $this->container->set('jsonapi.link_manager', $link_manager->reveal());

    $this->nodeType = NodeType::load('article');
  }


  /**
   * {@inheritdoc}
   */
  public function tearDown() {
    if ($this->node) {
      $this->node->delete();
    }
    if ($this->term1) {
      $this->term1->delete();
    }
    if ($this->term2) {
      $this->term2->delete();
    }
    if ($this->vocabulary) {
      $this->vocabulary->delete();
    }
    if ($this->user) {
      $this->user->delete();
    }
    if ($this->user2) {
      $this->user2->delete();
    }
  }

  /**
   * @covers ::normalize
   */
  public function testNormalize() {
    list($request, $resource_config) = $this->generateProphecies('node', 'article', 'id');
    $query = $this->prophesize(ParameterBag::class);
    $query->get('fields')->willReturn([
      'node--article' => 'title,type,uid',
      'user--user' => 'name',
    ]);
    $query->get('include')->willReturn('uid');
    $query->getIterator()->willReturn(new \ArrayIterator());
    $request->query = $query->reveal();
    $document_wrapper = $this->prophesize(DocumentWrapper::class);
    $document_wrapper->getData()->willReturn($this->node);

    $response = new ResourceResponse();
    $normalized = $this
      ->container
      ->get('serializer.normalizer.document_root.jsonapi')
      ->normalize(
        $document_wrapper->reveal(),
        'api_json',
        [
          'request' => $request->reveal(),
          'resource_config' => $resource_config->reveal(),
          'cacheable_metadata' => $response->getCacheableMetadata(),
        ]
      );
    $this->assertSame($normalized['data']['attributes']['title'], 'dummy_title');
    $this->assertEquals($normalized['data']['id'], 1);
    $this->assertSame([
      'data' => [
        'type' => 'node_type--node_type',
        'id' => 'article',
      ],
      'links' => [
        'self' => 'dummy_entity_link',
        'related' => 'dummy_entity_link',
      ],
    ], $normalized['data']['relationships']['type']);
    $this->assertTrue(!isset($normalized['data']['attributes']['created']));
    $this->assertSame('node--article', $normalized['data']['type']);
    $this->assertEquals([
      'data' => [
        'type' => 'user--user',
        'id' => $this->user->id(),
      ],
      'links' => [
        'self' => 'dummy_entity_link',
        'related' => 'dummy_entity_link',
      ],
    ], $normalized['data']['relationships']['uid']);
    $this->assertEquals($this->user->id(), $normalized['included'][0]['data']['id']);
    $this->assertEquals('user--user', $normalized['included'][0]['data']['type']);
    $this->assertEquals($this->user->label(), $normalized['included'][0]['data']['attributes']['name']);
    $this->assertTrue(!isset($normalized['included'][0]['data']['attributes']['created']));
    // Make sure that the cache tags for the includes and the requested entities
    // are bubbling as expected.
    $this->assertSame(['node:1', 'user:1'], $response->getCacheableMetadata()
      ->getCacheTags());
    $this->assertSame(Cache::PERMANENT, $response->getCacheableMetadata()
      ->getCacheMaxAge());
  }

  /**
   * @covers ::normalize
   */
  public function testNormalizeRelated() {
    list($request, $resource_config) = $this->generateProphecies('node', 'article', 'id', 'uid');
    $query = $this->prophesize(ParameterBag::class);
    $query->get('fields')->willReturn([
      'user--user' => 'name,roles',
    ]);
    $query->get('include')->willReturn('roles');
    $query->getIterator()->willReturn(new \ArrayIterator());
    $request->query = $query->reveal();
    $document_wrapper = $this->prophesize(DocumentWrapper::class);
    $author = $this->node->get('uid')->entity;
    $document_wrapper->getData()->willReturn($author);

    $response = new ResourceResponse();
    $normalized = $this
      ->container
      ->get('serializer.normalizer.document_root.jsonapi')
      ->normalize(
        $document_wrapper->reveal(),
        'api_json',
        [
          'request' => $request->reveal(),
          'resource_config' => $resource_config->reveal(),
          'cacheable_metadata' => $response->getCacheableMetadata(),
        ]
      );
    $this->assertSame($normalized['data']['attributes']['name'], 'user1');
    $this->assertEquals($normalized['data']['id'], 1);
    $this->assertEquals($normalized['data']['type'], 'user--user');
    // Make sure that the cache tags for the includes and the requested entities
    // are bubbling as expected.
    $this->assertSame(['user:1'], $response->getCacheableMetadata()
      ->getCacheTags());
    $this->assertSame(Cache::PERMANENT, $response->getCacheableMetadata()
      ->getCacheMaxAge());
  }

  /**
   * @covers ::normalize
   */
  public function testNormalizeUuid() {
    list($request, $resource_config) = $this->generateProphecies('node', 'article', 'uuid');
    $document_wrapper = $this->prophesize(DocumentWrapper::class);
    $document_wrapper->getData()->willReturn($this->node);
    $query = $this->prophesize(ParameterBag::class);
    $query->get('fields')->willReturn([
      'node--article' => 'title,type,uid',
      'user--user' => 'name',
    ]);
    $query->get('include')->willReturn('uid');
    $query->getIterator()->willReturn(new \ArrayIterator());
    $request->query = $query->reveal();

    $response = new ResourceResponse();
    $normalized = $this
      ->container
      ->get('serializer.normalizer.document_root.jsonapi')
      ->normalize(
        $document_wrapper->reveal(),
        'api_json',
        [
          'request' => $request->reveal(),
          'resource_config' => $resource_config->reveal(),
          'cacheable_metadata' => $response->getCacheableMetadata(),
        ]
      );
    $this->assertStringMatchesFormat($this->node->uuid(), $normalized['data']['id']);
    $this->assertEquals($this->node->type->entity->uuid(), $normalized['data']['relationships']['type']['data']['id']);
    $this->assertEquals($this->user->uuid(), $normalized['data']['relationships']['uid']['data']['id']);
    $this->assertEquals($this->user->uuid(), $normalized['included'][0]['data']['id']);
    // Make sure that the cache tags for the includes and the requested entities
    // are bubbling as expected.
    $this->assertSame(['node:1', 'user:1'], $response->getCacheableMetadata()
      ->getCacheTags());
  }

  /**
   * @covers ::normalize
   */
  public function testNormalizeException() {
    list($request, $resource_config) = $this->generateProphecies('node', 'article', 'id');
    $document_wrapper = $this->prophesize(DocumentWrapper::class);
    $document_wrapper->getData()->willReturn($this->node);
    $query = $this->prophesize(ParameterBag::class);
    $query->get('fields')->willReturn([
      'node--article' => 'title,type,uid',
      'user--user' => 'name',
    ]);
    $query->get('include')->willReturn('uid');
    $query->getIterator()->willReturn(new \ArrayIterator());
    $request->query = $query->reveal();

    $response = new ResourceResponse();
    $normalized = $this
      ->container
      ->get('serializer')
      ->serialize(
        new BadRequestHttpException('Lorem'),
        'api_json',
        [
          'request' => $request->reveal(),
          'resource_config' => $resource_config->reveal(),
          'cacheable_metadata' => $response->getCacheableMetadata(),
          'data_wrapper' => 'errors',
        ]
      );
    $normalized = Json::decode($normalized);
    $this->assertNotEmpty($normalized['errors']);
    $this->assertArrayNotHasKey('data', $normalized);
    $this->assertEquals(400, $normalized['errors'][0]['status']);
    $this->assertEquals('Lorem', $normalized['errors'][0]['detail']);
    $this->assertEquals(['info' => 'http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html#sec10.4.1'], $normalized['errors'][0]['links']);
  }

  /**
   * @covers ::normalize
   */
  public function testNormalizeConfig() {
    list($request, $resource_config) = $this->generateProphecies('node_type', 'node_type', 'id');
    $document_wrapper = $this->prophesize(DocumentWrapper::class);
    $document_wrapper->getData()->willReturn($this->nodeType);
    $query = $this->prophesize(ParameterBag::class);
    $query->get('fields')->willReturn([
      'node_type--node_type' => 'uuid,display_submitted',
    ]);
    $query->get('include')->willReturn(NULL);
    $query->getIterator()->willReturn(new \ArrayIterator());
    $request->query = $query->reveal();

    $response = new ResourceResponse();
    $normalized = $this
      ->container
      ->get('serializer.normalizer.document_root.jsonapi')
      ->normalize($document_wrapper->reveal(), 'api_json', [
        'request' => $request->reveal(),
        'resource_config' => $resource_config->reveal(),
        'cacheable_metadata' => $response->getCacheableMetadata(),
      ]);
    $this->assertTrue(empty($normalized['data']['attributes']['type']));
    $this->assertTrue(!empty($normalized['data']['attributes']['uuid']));
    $this->assertSame($normalized['data']['attributes']['display_submitted'], TRUE);
    $this->assertSame($normalized['data']['id'], 'article');
    $this->assertSame($normalized['data']['type'], 'node_type--node_type');
    // Make sure that the cache tags for the includes and the requested entities
    // are bubbling as expected.
    $this->assertSame(['config:node.type.article'], $response->getCacheableMetadata()
      ->getCacheTags());
  }

  /**
   * Try to POST a node and check if it exists afterwards.
   *
   * @covers ::denormalize
   */
  public function testDenormalize() {
    $payload = '{"type":"article", "data":{"attributes":{"title":"Testing article"}}}';

    list($request, $resource_config) = $this->generateProphecies('node', 'article', 'id');
    $node = $this
      ->container
      ->get('serializer.normalizer.document_root.jsonapi')
      ->denormalize(Json::decode($payload), DocumentRootNormalizerInterface::class, 'api_json', [
        'request' => $request->reveal(),
        'resource_config' => $resource_config->reveal(),
      ]);
    $this->assertInstanceOf('\Drupal\node\Entity\Node', $node);
    $this->assertSame('Testing article', $node->getTitle());
  }

  /**
   * Try to POST a node and check if it exists afterwards.
   *
   * @covers ::denormalize
   */
  public function testDenormalizeUuid() {
    $configurations = [
      // Good data.
      [
        [
          [$this->term2->uuid(), $this->term1->uuid()],
          $this->user2->uuid(),
        ],
        [
          [$this->term2->id(), $this->term1->id()],
          $this->user2->id(),
        ],
      ],
      // Bad data in first tag.
      [
        [
          ['invalid-uuid', $this->term1->uuid()],
          $this->user2->uuid(),
        ],
        [
          [$this->term1->id()],
          $this->user2->id(),
        ],
      ],
      // Bad data in user and first tag.
      [
        [
          ['invalid-uuid', $this->term1->uuid()],
          'also-invalid-uuid',
        ],
        [
          [$this->term1->id()],
          NULL
        ],
      ],
    ];

    foreach ($configurations as $configuration) {
      list($payload_data, $expected) = $this->denormalizeUuidProviderBuilder($configuration);
      $payload = Json::encode($payload_data);

      list($request, $resource_config) = $this->generateProphecies('node', 'article', 'uuid');
      $node = $this
        ->container
        ->get('serializer.normalizer.document_root.jsonapi')
        ->denormalize(Json::decode($payload), DocumentRootNormalizerInterface::class, 'api_json', [
          'request' => $request->reveal(),
          'resource_config' => $resource_config->reveal(),
        ]);

      /* @var \Drupal\node\Entity\Node $node */
      $this->assertInstanceOf('\Drupal\node\Entity\Node', $node);
      $this->assertSame('Testing article', $node->getTitle());
      if (!empty($expected['user_id'])) {
        $owner = $node->getOwner();
        $this->assertEquals($expected['user_id'], $owner->id());
      }
      $tags = $node->get('field_tags')->getValue();
      $this->assertEquals($expected['tag_ids'][0], $tags[0]['target_id']);
      if (!empty($expected['tag_ids'][1])) {
        $this->assertEquals($expected['tag_ids'][1], $tags[1]['target_id']);
      }
    }
  }

  /**
   * We cannot use a PHPUnit data provider because our data depends on $this.
   *
   * @param array $options
   *
   * @return array
   *   The test data.
   */
  protected function denormalizeUuidProviderBuilder($options) {
    list($input, $expected) = $options;
    list($input_tag_uuids, $input_user_uuid) = $input;
    list($expected_tag_ids, $expected_user_id) = $expected;

    return [
      [
        'type' => 'node--article',
        'data' => [
          'attributes' => [
            'title' => 'Testing article',
            'id' => '33095485-70D2-4E51-A309-535CC5BC0115',
          ],
          'relationships' => [
            'uid' => [
              'data' => [
                'type' => 'user--user',
                'id' => $input_user_uuid,
              ],
            ],
            'field_tags' => [
              'data' => [
                [
                  'type' => 'taxonomy_term--tags',
                  'id' => $input_tag_uuids[0],
                ],
                [
                  'type' => 'taxonomy_term--tags',
                  'id' => $input_tag_uuids[1],
                ],
              ],
            ],
          ],
        ],
      ],
      [
        'tag_ids' => $expected_tag_ids,
        'user_id' => $expected_user_id,
      ],
    ];
  }

  /**
   * Generates the prophecies for the mocked entity request.
   *
   * @param string $entity_type_id
   *   The ID of the entity type. Ex: node.
   * @param string $bundle_id
   *   The ID of the bundle. Ex: article.
   * @param string $id_key
   *   The ID to load the entity by. Ex: {id|uuid}.
   *
   * @return array
   *   A numeric array containing the request and the resource config mocks.
   */
  protected function generateProphecies($entity_type_id, $bundle_id, $id_key, $related_property = NULL) {
    $request = $this->prophesize(Request::class);
    $route = $this->prophesize(Route::class);
    $path = sprintf('/%s/%s', $entity_type_id, $bundle_id);
    $path = $related_property ?
      sprintf('%s/%s', $path, $related_property) :
      $path;
    $route->getPath()
      ->willReturn($path);
    $route->getRequirement('_entity_type')->willReturn($entity_type_id);
    $route->getRequirement('_bundle')->willReturn($bundle_id);
    $route->getDefault('_on_relationship')->willReturn(NULL);
    $request->get(RouteObjectInterface::ROUTE_OBJECT)
      ->willReturn($route->reveal());
    $resource_config = $this->prophesize(ResourceConfigInterface::CLASS);
    $resource_config->getTypeName()
      ->willReturn(sprintf('%s--%s', $entity_type_id, $bundle_id));
    $resource_config->getEntityTypeId()->willReturn($entity_type_id);
    $resource_config->getBundleId()->willReturn($bundle_id);
    $resource_config->getIdKey()->willReturn($id_key);
    \Drupal::configFactory()->getEditable('jsonapi.resource_info')
      ->set('id_field', $id_key)
      ->save();

    /* @var \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager */
    $entity_type_manager = $this->container->get('entity_type.manager');
    $serialization_class = $entity_type_manager->getDefinition($entity_type_id)
      ->getClass();
    $resource_config->getDeserializationTargetClass()
      ->willReturn($serialization_class);
    $resource_config->getStorage()
      ->willReturn($entity_type_manager->getStorage($entity_type_id));
    /* @var \Symfony\Component\HttpFoundation\RequestStack $request_stack */
    $request_stack = $this->container->get('request_stack');
    $request_stack->push($request->reveal());
    $this->container->set('request_stack', $request_stack);
    $this->container->get('serializer');

    return [$request, $resource_config];
  }

}
