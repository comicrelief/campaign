<?php

namespace Drupal\Tests\jsonapi\Unit\Configuration;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\jsonapi\Configuration\ResourceConfig;
use Drupal\Tests\UnitTestCase;

/**
 * Class ResourceConfigTest.
 *
 * @package \Drupal\jsonapi\Test\Unit
 *
 * @coversDefaultClass \Drupal\jsonapi\Configuration\ResourceConfig
 *
 * @group jsonapi
 */
class ResourceConfigTest extends UnitTestCase {

  /**
   * Test setters and getters.
   *
   * @covers ::setTypeName
   * @covers ::getTypeName
   * @covers ::setPath
   * @covers ::getPath
   * @covers ::setBundleId
   * @covers ::getBundleId
   *
   * @dataProvider settersAndGettersProvider
   */
  public function testSettersAndGetters($mutator, $accessor, $value) {
    $config_factory = $this->prophesize(ConfigFactoryInterface::class);
    $entity_type_manager = $this->prophesize(EntityTypeManagerInterface::class);
    $resource_config = new ResourceConfig($config_factory->reveal(), $entity_type_manager->reveal());
    $resource_config->{$mutator}($value);
    $this->assertEquals($value, $resource_config->{$accessor}());
  }

  /**
   * Provider for the setters test.
   *
   * @return array
   *   The data.
   */
  public function settersAndGettersProvider() {
    return [
      ['setTypeName', 'getTypeName', $this->getRandomGenerator()->name()],
      ['setPath', 'getPath', $this->getRandomGenerator()->name()],
      ['setBundleId', 'getBundleId', $this->getRandomGenerator()->name()],
    ];
  }

  /**
   * @covers ::getIdKey
   */
  public function testGetIdKey() {
    $config_factory = $this->prophesize(ConfigFactoryInterface::class);
    $config = $this->prophesize(ImmutableConfig::class);
    $name = $this->getRandomGenerator()->name();
    $config->get('id_field')->willReturn($name);
    $config_factory->get('jsonapi.resource_info')->willReturn($config->reveal());
    $entity_type_manager = $this->prophesize(EntityTypeManagerInterface::class);
    $resource_config = new ResourceConfig($config_factory->reveal(), $entity_type_manager->reveal());
    $this->assertEquals($name, $resource_config->getIdKey());
  }

}
