<?php

namespace Drupal\Tests\jsonapi\Unit\Normalizer\Value;

use Drupal\jsonapi\Normalizer\Value\FieldNormalizerValue;
use Drupal\Tests\UnitTestCase;

/**
 * Class FieldNormalizerValueTest.
 *
 * @package Drupal\Tests\jsonapi\Unit\Normalizer\Value
 *
 * @coversDefaultClass \Drupal\jsonapi\Normalizer\Value\FieldNormalizerValue
 * @group jsonapi
 */
class FieldNormalizerValueTest extends UnitTestCase {

  /**
   * @covers ::rasterizeValue
   * @dataProvider rasterizeValueProvider
   */
  public function testRasterizeValue($values, $cardinality, $expected) {
    $object = new FieldNormalizerValue($values, $cardinality);
    $this->assertEquals($expected, $object->rasterizeValue());
  }

  /**
   * Data provider for testRasterizeValue.
   */
  public function rasterizeValueProvider() {
    $uuid_raw = '4ae99eec-8b0e-41f7-9400-fbd65c174902';
    $uuid_value = $this->prophesize('\Drupal\jsonapi\Normalizer\Value\FieldItemNormalizerValueInterface');
    $uuid_value->rasterizeValue()->willReturn('4ae99eec-8b0e-41f7-9400-fbd65c174902');
    $uuid_value->getInclude()->willReturn(NULL);
    return [
      [[$uuid_value->reveal()], 1, $uuid_raw],
      [[$uuid_value->reveal(), $uuid_value->reveal()], -1, [$uuid_raw, $uuid_raw]],
    ];
  }

  /**
   * @covers ::rasterizeIncludes
   */
  public function testRasterizeIncludes() {
    $value = $this->prophesize('\Drupal\jsonapi\Normalizer\Value\FieldItemNormalizerValueInterface');
    $include = $this->prophesize('\Drupal\jsonapi\Normalizer\Value\EntityNormalizerValueInterface');
    $include->rasterizeValue()->willReturn('Lorem');
    $value->getInclude()->willReturn($include->reveal());
    $object = new FieldNormalizerValue([$value->reveal()], 1);
    $this->assertEquals(['Lorem'], $object->rasterizeIncludes());
  }

}
