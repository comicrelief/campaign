<?php

namespace Drupal\yamlform\Tests;

use Drupal\Component\Serialization\Yaml;
use Drupal\yamlform\Entity\YamlFormOptions;

/**
 * Tests for form option entity.
 *
 * @group YamlForm
 */
class YamlFormOptionsTest extends YamlFormTestBase {

  /**
   * Tests form options entity.
   */
  public function testYamlFormOptions() {
    // Check get element options.
    $yes_no_options = ['Yes' => 'Yes', 'No' => 'No'];
    $this->assertEqual(YamlFormOptions::getElementOptions(['#options' => $yes_no_options]), $yes_no_options);
    $this->assertEqual(YamlFormOptions::getElementOptions(['#options' => 'yes_no']), $yes_no_options);
    $this->assertEqual(YamlFormOptions::getElementOptions(['#options' => 'not-found']), []);

    $options = [
      'red' => 'Red',
      'white' => 'White',
      'blue' => 'Blue',
    ];

    // Check get element options for manually defined options.
    $this->assertEqual(YamlFormOptions::getElementOptions(['#options' => $options]), $options);

    /** @var \Drupal\yamlform\YamlFormOptionsInterface $yamlform_options */
    $yamlform_options = YamlFormOptions::create([
      'langcode' => 'en',
      'status' => TRUE,
      'id' => 'test_flag',
      'title' => 'Test flag',
      'options' => Yaml::encode($options),
    ]);
    $yamlform_options->save();

    // Check get options.
    $this->assertEqual($yamlform_options->getOptions(), $options);

    // Set invalid options.
    $yamlform_options->set('options', "not\nvalid\nyaml")->save();

    // Check invalid options.
    $this->assertFalse($yamlform_options->getOptions());

    // Check hook_yamlform_options_YAMLFORM_OPTIONS_ID_alter().
    $this->drupalGet('yamlform/test_options');
    $this->assertRaw('<option value="one">one</option><option value="two">two</option><option value="three">three</option>');
  }

}
