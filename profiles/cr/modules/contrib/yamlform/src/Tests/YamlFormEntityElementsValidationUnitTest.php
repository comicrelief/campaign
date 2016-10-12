<?php

namespace Drupal\yamlform\Tests;

use Drupal\Core\Url;
use Drupal\yamlform\YamlFormEntityElementsValidator;
use Drupal\KernelTests\KernelTestBase;

/**
 * Tests form entity elements validation.
 *
 * @group YamlForm
 */
class YamlFormEntityElementsValidationUnitTest extends KernelTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['system', 'yamlform', 'user'];

  /**
   * The form elements validator.
   *
   * @var \Drupal\yamlform\YamlFormEntityElementsValidator
   */
  protected $validator;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->validator = new YamlFormEntityElementsValidator();
  }

  /**
   * Tests validating elements.
   */
  public function testValidate() {
    $tests = [
      /*
      [
        'getElementsRaw' => '', // Elements.
        'getElementsOriginalRaw' => '', // Original elements.
        'messages' => [], // Validation error message.
      ],
      */

      // Check required.
      [
        'getElementsRaw' => '',
        'getElementsOriginalRaw' => '',
        'messages' => [
          'Elements are required',
        ],
      ],

      // Check elements are an array.
      [
        'getElementsRaw' => 'string',
        'messages' => [
          'Elements are not valid. YAML must contain an associative array of elements.',
        ],
      ],

      // Check duplicate names.
      [
        'getElementsRaw' => "name:
  '#type': textfield
duplicate:
  name:
    '#type': textfield",
        'messages' => [
          'Elements contain a duplicate element name <em class="placeholder">name</em> found on lines 1 and 4.',
        ],
      ],

      // Check duplicate name with single and double quotes.
      [
        'getElementsRaw' => "name :
  '#type': textfield
duplicate:
  name:
    '#type': textfield",
        'messages' => [
          'Elements contain a duplicate element name <em class="placeholder">name</em> found on lines 1 and 4.',
        ],
      ],

      // Check ignored properties.
      [
        'getElementsRaw' => "'tree':
  '#tree': true
  '#submit' : 'function_name'",
        'messages' => [
          'Elements contain an unsupported <em class="placeholder">#tree</em> property found on line 2.',
          'Elements contain an unsupported <em class="placeholder">#submit</em> property found on line 3.',
        ],
      ],

      // Check validate submissions.
      [
        'getElementsRaw' => "name_changed:
  '#type': 'textfield'",
        'getElementsOriginalRaw' => "name:
  '#type': 'textfield'",
        'messages' => [
          'The <em class="placeholder">name</em> element can not be removed because the <em class="placeholder">Test</em> form has <a href="http://example.com">results</a>.<ul><li><a href="http://example.com">Delete all submissions</a> to this form.</li><li><a href="/admin/modules">Enable the YAML Form UI module</a> and safely delete this element.</li><li>Hide this element by setting its <code>\'#access\'</code> property to <code>false</code>.</li></ul>',
        ],
      ],

      // Check validate hierarchy.
      [
        'getElementsRaw' => 'empty: empty',
        'getElementsOriginalRaw' => 'empty: empty',
        'getElementsInitializedAndFlattened' => [
          'leaf_parent' => [
            '#type' => 'textfield',
            '#yamlform_key' => 'leaf_parent',
            '#yamlform_children' => TRUE,
          ],
          'root' => [
            '#type' => 'yamlform_wizard_page',
            '#yamlform_key' => 'root',
            '#yamlform_parent_key' => TRUE,
          ],
        ],
        'messages' => [
          'The <em class="placeholder">leaf_parent</em> (textfield) is a form element that can not have any child elements.',
          'The <em class="placeholder">root</em> (wizard_page) is a root element that can not be used as child to another element',
        ],
      ],
/*
      // Check validate rendering.
      [
        'getElementsRaw' => "machine_name:
  '#type': 'machine_name'
  '#machine_name':
     source:
      broken",
        'getElementsOriginalRaw' => "machine_name:
  '#type': 'machine_name'
  '#machine_name':
     source:
      broken",
        'messages' => [
          'Unable to render elements, please view the below message and the error log.<ul><li>Query condition &#039;yamlform_submission.yamlform_id IN ()&#039; cannot be empty.</li></ul>',
        ],
      ],
*/
    ];

    // Check invalid YAML.
    // Test is customized depending on if the PECL YAML component is installed.
    // @see https://www.drupal.org/node/1920902#comment-11418117
    if (function_exists('yaml_parse')) {
      $test[] = [
        'getElementsRaw' => "not\nvalid\nyaml",
        'messages' => [
          'Elements are not valid. YAML must contain an associative array of elements.',
        ],
      ];
      $test[] = [
        'getElementsRaw' => "not:\nvalid\nyaml",
        'messages' => [
          'Elements are not valid. yaml_parse(): scanning error encountered during parsing: could not find expected &#039;:&#039; (line 3, column 1), context while scanning a simple key (line 2, column 1)',
        ],
      ];
    }
    else {
      $test[] = [
        'getElementsRaw' => "not\nvalid\nyaml",
        'messages' => [
          'Elements are not valid. Unable to parse at line 1 (near &quot;not&quot;).',
        ],
      ];
    }

    foreach ($tests as $test) {
      $test += [
        'getElementsRaw' => '',
        'getElementsOriginalRaw' => '',
        'getElementsInitializedAndFlattened' => [],
        'hasSubmissions' => TRUE,
        'label' => 'Test',
        'toUrl' => Url::fromUri('http://example.com'),
        'messages' => [],
      ];

      /** @var \Drupal\yamlform\YamlFormInterface $yamlform */
      $yamlform = $this->getMock('\Drupal\yamlform\YamlFormInterface');
      $methods = $test;
      unset($methods['message']);
      foreach ($methods as $method => $returnValue) {
        $yamlform->expects($this->any())
          ->method($method)
          ->will($this->returnValue($returnValue));
      }

      $messages = $this->validator->validate($yamlform);
      foreach ($messages as $index => $message) {
        $messages[$index] = (string) $message;
      }
      $this->assertEquals($messages, $test['messages']);
    }
  }

}
