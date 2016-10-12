<?php

namespace Drupal\Tests\yamlform\Unit;

use Drupal\yamlform\Utility\YamlFormOptionsHelper;
use Drupal\Tests\UnitTestCase;

/**
 * Tests form options utility.
 *
 * @group YamlFormUnit
 *
 * @coversDefaultClass \Drupal\yamlform\Utility\YamlFormOptionsHelper
 */
class YamlFormOptionsHelperTest extends UnitTestCase {

  /**
   * Tests YamlFormOptionsHelper::hasOption().
   *
   * @param string $value
   *   The value to run through YamlFormOptionsHelper::hasOption().
   * @param array $options
   *   The array to run through YamlFormOptionsHelper::hasOption().
   * @param string $expected
   *   The expected result from calling the function.
   *
   * @see YamlFormOptionsHelperl::hasOption()
   *
   * @dataProvider providerHasOption
   */
  public function testHasOption($value, array $options, $expected) {
    $result = YamlFormOptionsHelper::hasOption($value, $options);
    $this->assertEquals($expected, $result);
  }

  /**
   * Data provider for testHasOption().
   *
   * @see testHasOption()
   */
  public function providerHasOption() {
    $tests[] = [
      'value',
      ['value' => 'text'],
      TRUE,
    ];
    $tests[] = [
      'value',
      [],
      FALSE,
    ];
    $tests[] = [
      3,
      [1 => 'One', 2 => 'Two', 'optgroup' => [3 => 'Three']],
      TRUE,
    ];
    $tests[] = [
      'optgroup',
      [1 => 'One', 2 => 'Two', 'optgroup' => [3 => 'Three']],
      FALSE,
    ];
    return $tests;
  }

  /**
   * Tests YamlFormOptionsHelper::getOptionsText().
   *
   * @param array $values
   *   The array to run through YamlFormOptionsHelper::getOptionsText().
   * @param array $options
   *   The array to run through YamlFormOptionsHelper::getOptionsText().
   * @param string $expected
   *   The expected result from calling the function.
   *
   * @see YamlFormOptionsHelperl::getOptionsText()
   *
   * @dataProvider providerGetOptionsText
   */
  public function testGetOptionsText(array $values, array $options, $expected) {
    $result = YamlFormOptionsHelper::getOptionsText($values, $options);
    $this->assertEquals($expected, $result);
  }

  /**
   * Data provider for testGetOptionsText().
   *
   * @see testGetOptionsText()
   */
  public function providerGetOptionsText() {
    $tests[] = [
      ['value'],
      ['value' => 'text'],
      ['text'],
    ];
    $tests[] = [
      [1, 3],
      [1 => 'One', 2 => 'Two', 'optgroup' => [3 => 'Three']],
      ['One', 'Three'],
    ];
    return $tests;
  }

  /**
   * Tests YamlFormOptionsHelper::range().
   *
   * @param array $element
   *   The array to run through YamlFormOptionsHelper::range().
   * @param string $expected
   *   The expected result from calling the function.
   *
   * @see YamlFormOptionsHelperl::range()
   *
   * @dataProvider providerRange
   */
  public function testRange(array $element, $expected) {
    $element += [
      '#min' => 1,
      '#max' => 100,
      '#step' => 1,
      '#pad_length' => NULL,
      '#pad_str' => 0,
    ];

    $result = YamlFormOptionsHelper::range(
      $element['#min'],
      $element['#max'],
      $element['#step'],
      $element['#pad_length'],
      $element['#pad_str']
    );
    $this->assertEquals($expected, $result);
  }

  /**
   * Data provider for testRange().
   *
   * @see testRange()
   */
  public function providerRange() {
    $tests[] = [
      [
        '#min' => 1,
        '#max' => 3,
      ],
      [1 => 1, 2 => 2, 3 => 3],
    ];
    $tests[] = [
      [
        '#min' => 0,
        '#max' => 6,
        '#step' => 2,
      ],
      [0 => 0, 2 => 2, 4 => 4, 6 => 6],
    ];
    $tests[] = [
      [
        '#min' => 'A',
        '#max' => 'C',
      ],
      ['A' => 'A', 'B' => 'B', 'C' => 'C'],
    ];
    $tests[] = [
      [
        '#min' => 'a',
        '#max' => 'c',
      ],
      ['a' => 'a', 'b' => 'b', 'c' => 'c'],
    ];
    $tests[] = [
      [
        '#min' => 1,
        '#max' => 3,
        '#step' => 1,
        '#pad_length' => 2,
        '#pad_str' => 0,
      ],
      ['01' => '01', '02' => '02', '03' => '03'],
    ];
    return $tests;
  }

}
