<?php

namespace Drupal\Tests\yamlform\Unit;

use Drupal\yamlform\Utility\YamlFormElementHelper;
use Drupal\Tests\UnitTestCase;

/**
 * Tests form element utility.
 *
 * @group YamlFormUnit
 *
 * @coversDefaultClass \Drupal\yamlform\Utility\YamlFormElementHelper
 */
class YamlFormElementHelperTest extends UnitTestCase {

  /**
   * Tests YamlFormElementHelper::GetIgnoredProperties().
   *
   * @param array $element
   *   The array to run through YamlFormElementHelper::GetIgnoredProperties().
   * @param string $expected
   *   The expected result from calling the function.
   *
   * @see YamlFormElementHelperl::GetIgnoredProperties()
   *
   * @dataProvider providerGetIgnoredProperties
   */
  public function testGetIgnoredProperties(array $element, $expected) {
    $result = YamlFormElementHelper::getIgnoredProperties($element);
    $this->assertEquals($expected, $result);
  }

  /**
   * Data provider for testGetIgnoredProperties().
   *
   * @see testGetIgnoredProperties()
   */
  public function providerGetIgnoredProperties() {
    // Nothing ignored.
    $tests[] = [
      ['#value' => 'text'],
      [],
    ];
    // Ignore #tree.
    $tests[] = [
      ['#tree' => TRUE],
      ['#tree' => '#tree'],
    ];
    // Ignore #tree and #element_validate.
    $tests[] = [
      ['#tree' => TRUE, '#value' => 'text', '#element_validate' => 'some_function'],
      ['#tree' => '#tree', '#element_validate' => '#element_validate'],
    ];
    // Ignore #subelement__tree and #subelement__element_validate.
    $tests[] = [
      ['#subelement__tree' => TRUE, '#value' => 'text', '#subelement__element_validate' => 'some_function'],
      ['#subelement__tree' => '#subelement__tree', '#subelement__element_validate' => '#subelement__element_validate'],
    ];
    return $tests;
  }

  /**
   * Tests YamlFormElementHelper::RemoveIgnoredProperties().
   *
   * @param array $element
   *   The array to run through YamlFormElementHelper::RemoveIgnoredProperties().
   * @param string $expected
   *   The expected result from calling the function.
   *
   * @see YamlFormElementHelperl::RemoveIgnoredProperties()
   *
   * @dataProvider providerRemoveIgnoredProperties
   */
  public function testRemoveIgnoredProperties(array $element, $expected) {
    $result = YamlFormElementHelper::removeIgnoredProperties($element);
    $this->assertEquals($expected, $result);
  }

  /**
   * Data provider for testRemoveIgnoredProperties().
   *
   * @see testRemoveIgnoredProperties()
   */
  public function providerRemoveIgnoredProperties() {
    // Nothing removed.
    $tests[] = [
      ['#value' => 'text'],
      ['#value' => 'text'],
    ];
    // Remove #tree.
    $tests[] = [
      ['#tree' => TRUE],
      [],
    ];
    // Remove #tree and #element_validate.
    $tests[] = [
      ['#tree' => TRUE, '#value' => 'text', '#element_validate' => 'some_function'],
      ['#value' => 'text'],
    ];
    // Remove #subelement__tree and #subelement__element_validate.
    $tests[] = [
      ['#subelement__tree' => TRUE, '#value' => 'text', '#subelement__element_validate' => 'some_function'],
      ['#value' => 'text'],
    ];
    return $tests;
  }

}
