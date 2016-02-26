<?php

/**
 * @file
 * Contains \Drupal\Tests\inline_entity_form\Unit\EntityInlineFormTest.
 */

namespace Drupal\Tests\inline_entity_form\Unit;

use \Drupal\inline_entity_form\Form\EntityInlineForm;

class EntityInlineFormTest extends \PHPUnit_Framework_TestCase {

  /**
   * @dataProvider providerTestExtractArraySequence
   */
  public function testExtractArraySequence($array, $list, $expected) {
    $this->assertEquals($expected, EntityInlineForm::extractArraySequence($array, $list));
  }

  /**
   * Provides arrays to test EntityInlineForm::extractArraySequence().
   */
  public function providerTestExtractArraySequence() {
    $data = [];
    $data[] = [
      ['a' => ['b' => ['c' => 0]]],
      ['a', 'b', 'c'],
      ['a' => ['b' => ['c' => 0]]],
    ];
    $data[] = [
      ['a' => ['b' => ['c' => 0]]],
      ['a', 'b'],
      ['a' => ['b' => ['c' => 0]]],
    ];
    $data[] = [
      ['a' => ['b' => ['c' => 0]]],
      ['a'],
      ['a' => ['b' => ['c' => 0]]],
    ];
    $data[] = [
      ['a' => ['b' => ['c' => 0]]],
      ['d'],
      [],
    ];

    return $data;
  }

}
