<?php

/**
 * @file
 * Definition of Drupal\yamlform\Tests\YamlFormEntityInputsValidationUnitTest.
 */

namespace Drupal\yamlform\Tests;

use Drupal\Core\Url;
use Drupal\yamlform\YamlFormEntityInputsValidator;
use Drupal\KernelTests\KernelTestBase;

/**
 * Tests YAML form entity validation.
 *
 * @group YamlForm
 */
class YamlFormEntityInputsValidationUnitTest extends KernelTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['yamlform'];

  /**
   * The YAML form inputs validator.
   *
   * @var \Drupal\yamlform\YamlFormEntityInputsValidator
   */
  protected $validator;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->validator = new YamlFormEntityInputsValidator();

  }

  /**
   * Tests validating inputs.
   */
  public function testValidate() {
    $tests = [
      /*
      [
        '', // Inputs.
        '', // Original inputs.
        [], // Validation error message.
      ],
      */

      // Check required.
      [
        '',
        '',
        ['Inputs are required'],
      ],

      // Check invalid YAML.
      [
        "not\nvalid\nyaml",
        '',
        ['Inputs are not valid. Unable to parse at line 1 (near &quot;not&quot;).'],
      ],

      // Check inputs are an array.
      [
        'string',
        '',
        ['Inputs are not valid. YAML must contain an associative array of inputs.'],
      ],

      // Check duplicate names.
      [
        "name:
  '#type': textfield
duplicate:
  name:
    '#type': textfield",
        '',
        ['Inputs contain a duplicate element name <em class="placeholder">name</em> found on lines 1 and 4.'],
      ],

      // Check duplicate name with single and double quotes.
      [
        "'name' :
  '#type': textfield
duplicate:
  \"name\":
    '#type': textfield",
        '',
        ['Inputs contain a duplicate element name <em class="placeholder">name</em> found on lines 1 and 4.'],
      ],

      // Check ignored properties.
      [
        "'tree':
  '#tree': true
  '#submit' : 'function_name'",
        '',
        [
          'Inputs contain a unsupported <em class="placeholder">#tree</em> property found on line 2.',
          'Inputs contain a unsupported <em class="placeholder">#submit</em> property found on line 3.',
        ],
      ],

      // Check validate submissions.
      [
        "name_changed:
  '#type': 'textfield'",
        "name:
  '#type': 'textfield'",
        [
          'The <em class="placeholder">Test</em> form has <a href="http://example.com">results</a>. The <em class="placeholder">name</em> input can not be removed. You can either hide this input by setting its <code>\'#access\'</code> property to <code>false</code> or by <a href="http://example.com">deleting all the submitted results</a>.',
        ],
      ],

      // Check validate rendering.
      [
        "machine_name:
  '#type': 'machine_name'
  '#machine_name':
     source:
      broken",
        "machine_name:
  '#type': 'machine_name'
  '#machine_name':
     source:
      broken",
        [
          'Unable to render inputs, please view the below message and the error log.',
        ],
      ],
    ];

    foreach ($tests as $test) {
      $yamlform = $this->getMock('\Drupal\yamlform\YamlFormInterface');
      $yamlform->expects($this->any())
        ->method('getInputsRaw')
        ->will($this->returnValue($test[0]));
      $yamlform->expects($this->any())
        ->method('getOriginalInputsRaw')
        ->will($this->returnValue($test[1]));
      $yamlform->expects($this->any())
        ->method('hasSubmissions')
        ->will($this->returnValue(TRUE));
      $yamlform->expects($this->any())
        ->method('label')
        ->will($this->returnValue('Test'));
      $yamlform->expects($this->any())
        ->method('toUrl')
        ->will($this->returnValue(Url::fromUri('http://example.com')));

      $messages = $this->validator->validate($yamlform);
      foreach ($messages as $index => $message) {
        $messages[$index] = (string) $message;
      }
      $this->assertEquals($messages, $test[2]);
    }
  }

}
