<?php

namespace Drupal\Tests\yamlform\Unit;

use Drupal\Component\Serialization\Yaml;
use Drupal\yamlform\Utility\YamlFormTidy;
use Drupal\Tests\UnitTestCase;

/**
 * Tests form tidy utility.
 *
 * @group YamlFormUnit
 *
 * @coversDefaultClass \Drupal\yamlform\Utility\YamlFormTidy
 */
class YamlFormTidyTest extends UnitTestCase {

  /**
   * Tests YamlFormTidy tidy with YamlFormTidy::tidy().
   *
   * @param array $data
   *   The array to run through YamlFormTidy::tidy().
   * @param string $expected
   *   The expected result from calling the function.
   *
   * @see YamlFormTidy::tidy()
   *
   * @dataProvider providerTidy
   */
  public function testTidy(array $data, $expected) {
    $result = YamlFormTidy::tidy(Yaml::encode($data));
    $this->assertEquals($expected, $result);
  }

  /**
   * Data provider for testTidy().
   *
   * @see testTidy()
   */
  public function providerTidy() {
    $tests[] = [
      ['simple' => 'value'],
      "simple: value\n",
    ];
    $tests[] = [
      ['returns' => "line 1\nline 2"],
      "returns: |\n  line 1\n  line 2\n",
    ];
    $tests[] = [
      ['one two' => "line 1\nline 2"],
      "'one two': |\n  line 1\n  line 2\n",
    ];
    $tests[] = [
      ['array' => ['one', 'two']],
      "array:\n  - one\n  - two\n",
    ];
    return $tests;
  }

}
