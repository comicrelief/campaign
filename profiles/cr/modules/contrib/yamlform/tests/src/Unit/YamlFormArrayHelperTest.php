<?php

namespace Drupal\Tests\yamlform\Unit;

use Drupal\yamlform\Utility\YamlFormArrayHelper;
use Drupal\Tests\UnitTestCase;

/**
 * Tests form array utility.
 *
 * @group YamlFormUnit
 *
 * @coversDefaultClass \Drupal\yamlform\Utility\YamlFormArrayHelper
 */
class YamlFormArrayHelperTest extends UnitTestCase {

  /**
   * Tests converting arrays to readable string with YamlFormArrayHelper::toString().
   *
   * @param array $array
   *   The array to run through YamlFormArrayHelper::toString().
   * @param string $conjunction
   *   The $conjunction to run through YamlFormArrayHelper::toString().
   * @param string $expected
   *   The expected result from calling the function.
   * @param string $message
   *   The message to display as output to the test.
   *
   * @see YamlFormArrayHelper::toString()
   *
   * @dataProvider providerToString
   */
  public function testToString(array $array, $conjunction, $expected, $message) {
    $result = YamlFormArrayHelper::toString($array, $conjunction);
    $this->assertEquals($expected, $result, $message);
  }

  /**
   * Data provider for testToString().
   *
   * @see testToString()
   */
  public function providerToString() {
    $tests[] = [['Jack', 'Jill'], 'and', 'Jack and Jill', 'YamlFormArrayHelper::toString with Jack and Jill'];
    $tests[] = [['Jack', 'Jill'], 'or', 'Jack or Jill', 'YamlFormArrayHelper::toString with Jack or Jill'];
    $tests[] = [['Jack', 'Jill', 'Bill'], 'and', 'Jack, Jill, and Bill', 'YamlFormArrayHelper::toString with Jack and Jill'];
    $tests[] = [[''], 'and', '', 'YamlFormArrayHelper::toString with no one'];
    $tests[] = [['Jack'], 'and', 'Jack', 'YamlFormArrayHelper::toString with just Jack'];
    return $tests;
  }

}
