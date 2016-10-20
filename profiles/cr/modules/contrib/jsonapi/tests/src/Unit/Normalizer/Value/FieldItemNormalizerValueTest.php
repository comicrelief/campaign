<?php

namespace Drupal\Tests\jsonapi\Unit\Normalizer\Value;

use Drupal\jsonapi\Normalizer\Value\FieldItemNormalizerValue;
use Drupal\Tests\UnitTestCase;

/**
 * Class FieldItemNormalizerValueTest.
 *
 * @package Drupal\Tests\jsonapi\Unit\Normalizer\Value
 *
 * @coversDefaultClass \Drupal\jsonapi\Normalizer\Value\FieldItemNormalizerValue
 * @group jsonapi
 */
class FieldItemNormalizerValueTest extends UnitTestCase {

  /**
   * @covers ::rasterizeValue
   * @dataProvider rasterizeValueProvider
   */
  public function testRasterizeValue($values, $expected) {
    $object = new FieldItemNormalizerValue($values);
    $this->assertEquals($expected, $object->rasterizeValue());
  }

  /**
   * Provider for testRasterizeValue.
   */
  public function rasterizeValueProvider() {
    return [
      [['value' => 1], 1],
      [['value' => 1, 'safe_value' => 1], ['value' => 1, 'safe_value' => 1]],
      [[], []],
      [[NULL], NULL],
    ];
  }

}
