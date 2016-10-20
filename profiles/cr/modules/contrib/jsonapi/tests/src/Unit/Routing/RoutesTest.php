<?php

namespace Drupal\Tests\jsonapi\Unit\Routing;

use Drupal\Core\Authentication\AuthenticationCollectorInterface;
use Drupal\jsonapi\Plugin\JsonApiResourceManager;
use Drupal\jsonapi\Routing\Routes;
use Drupal\Tests\UnitTestCase;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class RoutesTest.
 *
 * @package Drupal\Tests\jsonapi\Unit\Routing
 *
 * @coversDefaultClass \Drupal\jsonapi\Routing\Routes
 *
 * @group jsonapi
 */
class RoutesTest extends UnitTestCase {

  /**
   * List of routes objects for the different scenarios.
   *
   * @var Routes[]
   */
  protected $routes;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    // Mock the resource manager to have some resources available.
    $resource_plugin_manager = $this->prophesize(JsonApiResourceManager::class);
    $resource_plugin_manager->getDefinitions()->willReturn([
      'bundle:api.dynamic.resource_type_1' => [
        'id' => 'bundle:api.dynamic.resource_type_1',
        'entityType' => 'entity_type_1',
        'bundle' => 'bundle_1_1',
        'hasBundle' => TRUE,
        'type' => 'resource_type_1',
        'data' => [
          'prefix' => 'api',
          'partialPath' => '/api/entity_type_1/bundle_path_1',
        ],
        'schema' => [
          'prefix' => 'schema',
          'partialPath' => '/schema/entity_type_1/bundle_path_1',
        ],
        'controller' => 'MyCustomController',
        'permission' => 'access content',
        'enabled' => TRUE,
      ],
      'bundle:api.dynamic.resource_type_2' => [
        'id' => 'bundle:api.dynamic.resource_type_2',
        'entityType' => 'entity_type_2',
        'bundle' => 'bundle_2_2',
        'hasBundle' => TRUE,
        'type' => 'resource_type_2',
        'data' => [
          'prefix' => 'api',
          'partialPath' => '/api/entity_type_2/bundle_path_2',
        ],
        'schema' => [
          'prefix' => 'schema',
          'partialPath' => '/schema/entity_type_2/bundle_path_2',
        ],
        'controller' => 'MyCustomController',
        'permission' => 'access content',
        'enabled' => FALSE,
      ],
    ]);
    $container = $this->prophesize(ContainerInterface::class);
    $container->get('plugin.manager.resource.processor')->willReturn($resource_plugin_manager->reveal());
    $auth_collector = $this->prophesize(AuthenticationCollectorInterface::class);
    $auth_collector->getSortedProviders()->willReturn([
      'lorem' => [],
      'ipsum' => [],
    ]);
    $container->get('authentication_collector')->willReturn($auth_collector->reveal());

    $this->routes['ok'] = Routes::create($container->reveal());
  }


  /**
   * @covers ::routes
   */
  public function testRoutesCollection() {
    // Get the route collection and start making assertions.
    $routes = $this->routes['ok']->routes();

    // Make sure that there are 4 routes for each resource.
    $this->assertEquals(6, $routes->count());

    $iterator = $routes->getIterator();
    // Check the collection route.
    /** @var \Symfony\Component\Routing\Route $route */
    $route = $iterator->offsetGet('api.dynamic.resource_type_1.collection');
    $this->assertSame('/api/entity_type_1/bundle_path_1', $route->getPath());
    $this->assertSame('entity_type_1', $route->getRequirement('_entity_type'));
    $this->assertSame('bundle_1_1', $route->getRequirement('_bundle'));
    $this->assertSame(['lorem', 'ipsum'], $route->getOption('_auth'));
    $this->assertEquals(['GET', 'POST'], $route->getMethods());
    $this->assertSame('MyCustomController', $route->getDefault(RouteObjectInterface::CONTROLLER_NAME));
    $this->assertSame('Drupal\jsonapi\Resource\DocumentWrapperInterface', $route->getOption('serialization_class'));
    $this->assertFalse($iterator->offsetExists('api.dynamic.resource_type_2.collection'));
  }

  /**
   * @covers ::routes
   */
  public function testRoutesIndividual() {
    // Get the route collection and start making assertions.
    $iterator = $this->routes['ok']->routes()->getIterator();

    // Check the individual route.
    /** @var \Symfony\Component\Routing\Route $route */
    $route = $iterator->offsetGet('api.dynamic.resource_type_1.individual');
    $this->assertSame('/api/entity_type_1/bundle_path_1/{entity_type_1}', $route->getPath());
    $this->assertSame('entity_type_1', $route->getRequirement('_entity_type'));
    $this->assertSame('bundle_1_1', $route->getRequirement('_bundle'));
    $this->assertEquals(['GET', 'PATCH', 'DELETE'], $route->getMethods());
    $this->assertSame('MyCustomController', $route->getDefault(RouteObjectInterface::CONTROLLER_NAME));
    $this->assertSame('Drupal\jsonapi\Resource\DocumentWrapperInterface', $route->getOption('serialization_class'));
    $this->assertSame(['lorem', 'ipsum'], $route->getOption('_auth'));
    $this->assertEquals(['entity_type_1' => ['type' => 'entity:entity_type_1']], $route->getOption('parameters'));
    $this->assertFalse($iterator->offsetExists('api.dynamic.resource_type_2.individual'));
  }

  /**
   * @covers ::routes
   */
  public function testRoutesRelated() {
    // Get the route collection and start making assertions.
    $iterator = $this->routes['ok']->routes()->getIterator();

    // Check the related route.
    /** @var \Symfony\Component\Routing\Route $route */
    $route = $iterator->offsetGet('api.dynamic.resource_type_1.related');
    $this->assertSame('/api/entity_type_1/bundle_path_1/{entity_type_1}/{related}', $route->getPath());
    $this->assertSame('entity_type_1', $route->getRequirement('_entity_type'));
    $this->assertSame('bundle_1_1', $route->getRequirement('_bundle'));
    $this->assertEquals(['GET'], $route->getMethods());
    $this->assertSame('MyCustomController', $route->getDefault(RouteObjectInterface::CONTROLLER_NAME));
    $this->assertSame(['lorem', 'ipsum'], $route->getOption('_auth'));
    $this->assertEquals(['entity_type_1' => ['type' => 'entity:entity_type_1']], $route->getOption('parameters'));
    $this->assertFalse($iterator->offsetExists('api.dynamic.resource_type_2.related'));
  }

  /**
   * @covers ::routes
   */
  public function testRoutesRelationships() {
    // Get the route collection and start making assertions.
    $iterator = $this->routes['ok']->routes()->getIterator();

    // Check the relationships route.
    /** @var \Symfony\Component\Routing\Route $route */
    $route = $iterator->offsetGet('api.dynamic.resource_type_1.relationship');
    $this->assertSame('/api/entity_type_1/bundle_path_1/{entity_type_1}/relationships/{related}', $route->getPath());
    $this->assertSame('entity_type_1', $route->getRequirement('_entity_type'));
    $this->assertSame('bundle_1_1', $route->getRequirement('_bundle'));
    $this->assertEquals(['GET', 'POST', 'PATCH', 'DELETE'], $route->getMethods());
    $this->assertSame('MyCustomController', $route->getDefault(RouteObjectInterface::CONTROLLER_NAME));
    $this->assertSame(['lorem', 'ipsum'], $route->getOption('_auth'));
    $this->assertEquals(['entity_type_1' => ['type' => 'entity:entity_type_1']], $route->getOption('parameters'));
    $this->assertSame('Drupal\Core\Field\EntityReferenceFieldItemList', $route->getOption('serialization_class'));
    $this->assertFalse($iterator->offsetExists('api.dynamic.resource_type_2.relationship'));
  }

}
