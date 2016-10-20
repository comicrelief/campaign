<?php

namespace Drupal\Tests\jsonapi\Unit\Routing\Param;

use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\jsonapi\Routing\Param\Filter;
use Drupal\Tests\UnitTestCase;


/**
 * Class FilterTest.
 *
 * @package Drupal\jsonapi\Test\Unit
 *
 * @coversDefaultClass \Drupal\jsonapi\Routing\Param\Filter
 *
 * @group jsonapi
 * @group jsonapi_params
 */
class FilterTest extends UnitTestCase {

  /**
   * @covers ::get
   * @dataProvider getProvider
   */
  public function testGet($original, $expected) {
    $pager = new Filter(
      $original,
      'lorem',
      $this->prophesize(EntityFieldManagerInterface::class)->reveal());
    $this->assertEquals($expected, $pager->get());
  }

  /**
   * Data provider for testGet.
   */
  public function getProvider() {
    return [
      [ // Tests filter[0][field]=foo&filter[0][value]=bar
        [['field' => 'foo', 'value' => 'bar']],
        [['condition' => [ 'field' => 'foo', 'value' => 'bar', 'operator' => '=']]],
      ],
      [ // Tests filter[foo][value]=bar
        ['foo' => ['value' => 'bar']],
        ['foo' => ['condition' => [ 'field' => 'foo', 'value' => 'bar', 'operator' => '=']]],
      ],
      [ // Tests filter[foo][value]=bar&filter[foo][operator]=>
        ['foo' => ['value' => 'bar', 'operator' => '>']],
        ['foo' => ['condition' => [ 'field' => 'foo', 'value' => 'bar', 'operator' => '>']]],
      ],
      [ // Tests filter[foo][value][]=1&filter[foo][value][]=2&filter[foo][value][]=3&filter[foo][operator]=NOT IN
        ['foo' => ['value' => ['1', '2', '3'], 'operator' => 'NOT IN']],
        ['foo' => ['condition' => [ 'field' => 'foo', 'value' => ['1', '2', '3'], 'operator' => 'NOT IN']]],
      ],
      [ // Tests filter[foo][value][]=1&filter[foo][value][]=10&filter[foo][operator]=BETWEEN
        ['foo' => ['value' => ['1', '10'], 'operator' => 'BETWEEN']],
        ['foo' => ['condition' => [ 'field' => 'foo', 'value' => ['1', '10'], 'operator' => 'BETWEEN']]],
      ],
      [ // Tests filter[0][field]=foo&filter[0][value]=1&filter[0][operator]=>
        [['field' => 'foo', 'value' => '1', 'operator' => '>']],
        [['condition' => [ 'field' => 'foo', 'value' => '1', 'operator' => '>']]],
      ],
      [ // Tests filter[0][condition][field]=foo&filter[0][condition][value]=1&filter[0][condition][operator]=>
        [['condition' => [ 'field' => 'foo', 'value' => '1', 'operator' => '>']]],
        [['condition' => [ 'field' => 'foo', 'value' => '1', 'operator' => '>']]],
      ],
      [ // Tests filter[0][field]=foo&filter[0][value][]=bar&filter[0][value][]=baz
        [['field' => 'foo', 'value' => ['bar', 'baz']]],
        [['condition' => [ 'field' => 'foo', 'value' => ['bar', 'baz'], 'operator' => '=']]],
      ],
      [
        [ // Tests filter[0][field]=foo&filter[0][value]=bar&filter[1][condition][field]=baz&filter[1][condition][value]=zab&filter[1][condition][operator]=<>
          0 => ['field' => 'foo', 'value' => 'bar'],
          1 => ['condition' => [ 'field' => 'baz', 'value' => 'zab', 'operator' => '<>']],
        ],
        [
          0 => ['condition' => [ 'field' => 'foo', 'value' => 'bar', 'operator' => '=']],
          1 => ['condition' => [ 'field' => 'baz', 'value' => 'zab', 'operator' => '<>']],
        ],
      ],
      [
        [ // Tests filter[zero][field]=foo&filter[zero][value]=bar&filter[one][condition][field]=baz&filter[one][condition][value]=zab&filter[one][condition][operator]=<>
          'zero' => ['field' => 'foo', 'value' => 'bar'],
          'one' => ['condition' => [ 'field' => 'baz', 'value' => 'zab', 'operator' => '<>']],
        ],
        [
          'zero' => ['condition' => [ 'field' => 'foo', 'value' => 'bar', 'operator' => '=']],
          'one' => ['condition' => [ 'field' => 'baz', 'value' => 'zab', 'operator' => '<>']],
        ],
      ],
    ];
  }

  /**
   * @covers ::get
   * @expectedException \Drupal\jsonapi\Error\SerializableHttpException
   */
  public function testGetFail() {
    $pager = new Filter(
      'lorem',
      'ipsum',
      $this->prophesize(EntityFieldManagerInterface::class)->reveal()
    );
    $pager->get();
  }

}
