<?php

namespace Drupal\Tests\yamlform\Unit;

use Drupal\yamlform\Utility\YamlFormHelper;
use Drupal\Tests\UnitTestCase;

/**
 * Tests form helper utility.
 *
 * @group YamlFormUnit
 *
 * @coversDefaultClass \Drupal\yamlform\Utility\YamlFormHelper
 */
class YamlFormHelperTest extends UnitTestCase {

  /**
   * Tests YamlFormHelper with YamlFormHelper::cleanupFormStateValues().
   *
   * @param array $values
   *   The array to run through YamlFormHelper::cleanupFormStateValues().
   * @param array $keys
   *   (optional) An array of custom keys to be removed.
   * @param string $expected
   *   The expected result from calling the function.
   *
   * @see YamlFormHelper::cleanupFormStateValues()
   *
   * @dataProvider providerCleanupFormStateValues
   */
  public function testCleanupFormStateValues(array $values, array $keys, $expected) {
    $result = YamlFormHelper::cleanupFormStateValues($values, $keys);
    $this->assertEquals($expected, $result);
  }

  /**
   * Data provider for testCleanupFormStateValues().
   *
   * @see testCleanupFormStateValues()
   */
  public function providerCleanupFormStateValues() {
    $tests[] = [['key' => 'value'], [], ['key' => 'value']];
    $tests[] = [['key' => 'value', 'form_token' => 'ignored'], [], ['key' => 'value']];
    $tests[] = [['key' => 'value', 'form_token' => 'ignored'], ['key'], []];
    return $tests;
  }

}
