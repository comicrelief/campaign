<?php

namespace Drupal\Tests\jsonapi\Unit\Normalizer\Value;

use Drupal\jsonapi\Configuration\ResourceConfigInterface;
use Drupal\jsonapi\LinkManager\LinkManagerInterface;
use Drupal\jsonapi\Normalizer\Value\RelationshipItemNormalizerValue;
use Drupal\jsonapi\Normalizer\Value\RelationshipNormalizerValue;
use Drupal\jsonapi\Normalizer\Value\FieldItemNormalizerValue;
use Drupal\Tests\UnitTestCase;
use Prophecy\Argument;

/**
 * Class EntityReferenceNormalizerValueTes.
 *
 * @package Drupal\Tests\jsonapi\Unit\Normalizer\Value
 *
 * @coversDefaultClass \Drupal\jsonapi\Normalizer\Value\RelationshipNormalizerValue
 * @group jsonapi
 */
class RelationshipNormalizerValueTest extends UnitTestCase {

  /**
   * @covers ::rasterizeValue
   * @dataProvider rasterizeValueProvider
   */
  public function testRasterizeValue($values, $cardinality, $expected) {
    $link_manager = $this->prophesize(LinkManagerInterface::class);
    $link_manager
      ->getEntityLink(Argument::any(), Argument::any(), Argument::type('array'), Argument::type('string'))
      ->willReturn('dummy_entity_link');
    $object = new RelationshipNormalizerValue($values, $cardinality, [
      'link_manager' => $link_manager->reveal(),
      'host_entity_id' => 'lorem',
      'resource_config' => $this->prophesize(ResourceConfigInterface::class)->reveal(),
      'field_name' => 'ipsum',
    ]);
    $this->assertEquals($expected, $object->rasterizeValue());
  }

  /**
   * Data provider fortestRasterizeValue.
   */
  public function rasterizeValueProvider() {
    $uid_raw = 1;
    $uid1 = $this->prophesize(RelationshipItemNormalizerValue::class);
    $uid1->rasterizeValue()->willReturn(['type' => 'user', 'id' => $uid_raw++]);
    $uid1->getInclude()->willReturn(NULL);
    $uid2 = $this->prophesize(RelationshipItemNormalizerValue::class);
    $uid2->rasterizeValue()->willReturn(['type' => 'user', 'id' => $uid_raw]);
    $uid2->getInclude()->willReturn(NULL);
    $links = [
      'self' => 'dummy_entity_link',
      'related' => 'dummy_entity_link',
    ];
    return [
      [[$uid1->reveal()], 1, [
        'data' => ['type' => 'user', 'id' => 1],
        'links' => $links,
      ]],
      [
        [$uid1->reveal(), $uid2->reveal()], 2, [
          'data' => [
            ['type' => 'user', 'id' => 1],
            ['type' => 'user', 'id' => 2],
          ],
          'links' => $links,
        ],
      ],
    ];
  }

  /**
   * @covers ::rasterizeValue
   *
   * @expectedException \RuntimeException
   */
  public function testRasterizeValueFails() {
    $uid1 = $this->prophesize(FieldItemNormalizerValue::class);
    $uid1->rasterizeValue()->willReturn(1);
    $uid1->getInclude()->willReturn(NULL);
    $link_manager = $this->prophesize(LinkManagerInterface::class);
    $link_manager
      ->getEntityLink(Argument::any(), Argument::any(), Argument::type('array'), Argument::type('string'))
      ->willReturn('dummy_entity_link');
    $object = new RelationshipNormalizerValue([$uid1->reveal()], 1, [
      'link_manager' => $link_manager->reveal(),
      'host_entity_id' => 'lorem',
      'resource_config' => $this->prophesize(ResourceConfigInterface::class)->reveal(),
      'field_name' => 'ipsum',
    ]);
    $object->rasterizeValue();
    // If the exception was not thrown, then the following fails.
    $this->assertTrue(FALSE);
  }

}
