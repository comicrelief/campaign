<?php

namespace Drupal\Tests\jsonapi\Unit\Normalizer\Value;

use Drupal\jsonapi\Configuration\ResourceConfigInterface;
use Drupal\jsonapi\Normalizer\Value\RelationshipItemNormalizerValue;
use Drupal\Tests\UnitTestCase;

/**
 * Class EntityReferenceItemNormalizerValueTest.
 *
 * @package Drupal\Tests\jsonapi\Unit\Normalizer\Value
 *
 * @coversDefaultClass \Drupal\jsonapi\Normalizer\Value\RelationshipItemNormalizerValue
 * @group jsonapi
 */
class RelationshipItemNormalizerValueTest extends UnitTestCase {

  /**
   * @covers ::rasterizeValue
   * @dataProvider rasterizeValueProvider
   */
  public function testRasterizeValue($values, $resource_type, $expected) {
    $resource = $this->prophesize(ResourceConfigInterface::class);
    $resource->getTypeName()->willReturn($resource_type);
    $object = new RelationshipItemNormalizerValue($values, $resource->reveal());
    $this->assertEquals($expected, $object->rasterizeValue());
  }

  /**
   * Data provider for testRasterizeValue.
   */
  public function rasterizeValueProvider() {
    return [
      [['target_id' => 1], 'node', ['type' => 'node', 'id' => 1]],
      [['value' => 1], 'node', ['type' => 'node', 'id' => 1]],
      [[1], 'node', ['type' => 'node', 'id' => 1]],
      [[], 'node', []],
      [[NULL], 'node', NULL],
    ];
  }
}
