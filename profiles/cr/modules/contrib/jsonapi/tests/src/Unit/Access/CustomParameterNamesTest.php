<?php

namespace Drupal\Tests\jsonapi\Unit\Access;

use Drupal\jsonapi\Access\CustomParameterNames;
use Symfony\Component\HttpFoundation\Request;

/**
 * @coversDefaultClass \Drupal\jsonapi\Access\CustomParameterNames
 * @group jsonapi
 */
class CustomParameterNamesTest extends \PHPUnit_Framework_TestCase {

  /**
   * @dataProvider providerTestJsonApiParamsValidation
   * @covers ::access
   * @covers ::validate
   */
  public function testJsonApiParamsValidation($name, $valid) {
    $access_checker = new CustomParameterNames();

    $request = new Request();
    $request->attributes->set('_json_api_params', [$name => '123']);
    $result = $access_checker->access($request);

    if ($valid) {
      $this->assertTrue($result->isAllowed());
    }
    else {
      $this->assertFalse($result->isAllowed());
    }
  }

  public function providerTestJsonApiParamsValidation() {
    // Copied from http://jsonapi.org/format/upcoming/#document-member-names.
    $data = [];
    $data['alphanumeric-lowercase'] = ['12kittens', TRUE];
    $data['alphanumeric-uppercase'] = ['12KITTENS', TRUE];
    $data['alphanumeric-mixed'] = ['12KiTtEnS', TRUE];
    $data['unicode-above-u+0080'] = ['12🐱🐱', TRUE];
    $data['hyphen-start'] = ['-kittens', FALSE];
    $data['hyphen-middle'] = ['kitt-ens', TRUE];
    $data['hyphen-end'] = ['kittens-', FALSE];
    $data['lowline-start'] = ['_kittens', FALSE];
    $data['lowline-middle'] = ['kitt_ens', TRUE];
    $data['lowline-end'] = ['kittens_', FALSE];
    $data['space-start'] = [' kittens', FALSE];
    $data['space-middle'] = ['kitt ens', TRUE];
    $data['space-end'] = ['kittens ', FALSE];

    $unsafe_chars = [
      '+',
      ',',
      '.',
      '[',
      ']',
      '!',
      '”',
      '#',
      '$',
      '%',
      '&',
      '’',
      '(',
      ')',
      '*',
      '/',
      ':',
      ';',
      '<',
      '=',
      '>',
      '?',
      '@',
      '“',
      '^',
      '`',
      '{',
      '|',
      '}',
      '~',
    ];
    foreach ($unsafe_chars as $unsafe_char) {
      $data['unsafe-' . $unsafe_char] = ['kitt' . $unsafe_char . 'ens', FALSE];
    }

    return $data;
  }

}
