<?php

namespace Drupal\Tests\jsonapi\Unit\Routing\Param;

use Drupal\jsonapi\Routing\Param\Sort;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Class SortTest.
 *
 * @package Drupal\jsonapi\Test\Unit
 *
 * @coversDefaultClass \Drupal\jsonapi\Routing\Param\Sort
 * @group jsonapi
 */
class SortTest extends UnitTestCase {

  /**
   * @covers ::get
   * @dataProvider getProvider
   */
  public function testGet($original, $expected) {
    $sort = new Sort($original);
    $this->assertEquals($expected, $sort->get());
  }

  /**
   * Data provider for testGet.
   */
  public function getProvider() {
    return [
      ['lorem', [['field' => 'lorem', 'direction' => 'ASC', 'langcode' => NULL]]],
      ['-lorem', [['field' => 'lorem', 'direction' => 'DESC', 'langcode' => NULL]]],
      ['-lorem,ipsum', [
        ['field' => 'lorem', 'direction' => 'DESC', 'langcode' => NULL],
        ['field' => 'ipsum', 'direction' => 'ASC', 'langcode' => NULL]
      ]],
      ['-lorem,-ipsum', [
        ['field' => 'lorem', 'direction' => 'DESC', 'langcode' => NULL],
        ['field' => 'ipsum', 'direction' => 'DESC', 'langcode' => NULL]
      ]],
      [[
        ['field' => 'lorem', 'langcode' => NULL],
        ['field' => 'ipsum', 'langcode' => 'ca'],
        ['field' => 'dolor', 'direction' => 'ASC', 'langcode' => 'ca'],
        ['field' => 'sit', 'direction' => 'DESC', 'langcode' => 'ca'],
      ], [
        ['field' => 'lorem', 'direction' => 'ASC', 'langcode' => NULL],
        ['field' => 'ipsum', 'direction' => 'ASC', 'langcode' => 'ca'],
        ['field' => 'dolor', 'direction' => 'ASC', 'langcode' => 'ca'],
        ['field' => 'sit', 'direction' => 'DESC', 'langcode' => 'ca'],
      ]],
    ];
  }

  /**
   * @covers ::get
   * @dataProvider getFailProvider
   * @expectedException \Drupal\jsonapi\Error\SerializableHttpException
   */
  public function testGetFail($input) {
    $sort = new Sort($input);
    $sort->get();
  }

  /**
   * Data provider for testGetFail.
   */
  public function getFailProvider() {
    return [
      [[['lorem']]],
      [''],
    ];
  }

}
