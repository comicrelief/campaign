<?php

namespace Drupal\Tests\jsonapi\Unit\Plugin\Deriver;

use Drupal\jsonapi\Plugin\Deriver\ResourceDeriver;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\jsonapi\Configuration\ResourceConfigInterface;
use Drupal\jsonapi\Configuration\ResourceManagerInterface;
use Drupal\Tests\UnitTestCase;
use Prophecy\Argument;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ResourceDeriverTest.
 *
 * @package Drupal\Tests\jsonapi\Unit\Plugin\Deriver
 *
 * @coversDefaultClass \Drupal\jsonapi\Plugin\Deriver\ResourceDeriver
 *
 * @group jsonapi
 */
class ResourceDeriverTest extends UnitTestCase {

  /**
   * The deriver under test.
   *
   * @var ResourceDeriver
   */
  protected $deriver;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    // Mock the resource manager to have some resources available.
    $resource_manager = $this->prophesize(ResourceManagerInterface::class);

    // Create some resource mocks for the manager.
    $resource_config = $this->prophesize(ResourceConfigInterface::class);
    $global_config = $this->prophesize(ImmutableConfig::class);
    $global_config->get('prefix')->willReturn('api');
    $global_config->get('schema_prefix')->willReturn('schema');
    $resource_config->getGlobalConfig()->willReturn($global_config->reveal());
    $resource_config->getEntityTypeId()->willReturn('entity_type_1');
    $resource_config->getBundleId()->willReturn('bundle_1_1');
    // Make sure that we're not coercing the bundle into the path, they can be
    // different in the future.
    $resource_config->getPath()->willReturn('/entity_type_1/bundle_path_1');
    $resource_config->getTypeName()->willReturn('resource_type_1');
    $resource_manager->all()->willReturn([$resource_config->reveal()]);
    $resource_manager->hasBundle(Argument::type('string'))->willReturn(FALSE);

    $container = $this->prophesize(ContainerInterface::class);
    $container->get('jsonapi.resource.manager')->willReturn($resource_manager->reveal());
    $this->deriver = ResourceDeriver::create($container->reveal(), 'bundle');
  }


  /**
   * @covers ::getDerivativeDefinitions
   */
  public function testGetDerivativeDefinitions() {
    $expected = ['api.dynamic.resource_type_1' => [
      'id' => 'api.dynamic.resource_type_1',
      'entityType' => 'entity_type_1',
      'bundle' => 'bundle_1_1',
      'hasBundle' => FALSE,
      'type' => 'resource_type_1',
      'data' => [
        'prefix' => 'api',
        'partialPath' => '/api/entity_type_1/bundle_path_1',
      ],
      'schema' => [
        'prefix' => 'schema',
        'partialPath' => '/schema/entity_type_1/bundle_path_1',
      ],
      'permission' => 'access content',
      'controller' => '\\Drupal\\jsonapi\\RequestHandler::handle',
      'enabled' => TRUE,
    ]];
    $actual = $this->deriver->getDerivativeDefinitions([
      'permission' => 'access content',
      'controller' => '\Drupal\jsonapi\RequestHandler::handle',
      'enabled' => TRUE,
    ]);
    $this->assertArrayEquals($expected, $actual);
  }

}
