<?php

/**
 * @file
 * Definition of Drupal\yamlform\test\YamlFormTest.
 */

namespace Drupal\yamlform\Tests;

/**
 * Tests for YAML form entity.
 *
 * @group YamlForm
 */
class YamlFormTest extends YamlFormTestBase {

  /**
   * Tests YAML form entity.
   */
  public function testYamlForm() {
    /** @var \Drupal\yamlform\YamlFormInterface $yamlform */
    list($yamlform) = $this->createYamlFormWithSubmissions();

    // Check get inputs.
    $inputs = $yamlform->getInputs();
    $this->assert(is_array($inputs));

    // Check getElements.
    $columns = $yamlform->getElements();
    $this->assertEqual(array_keys($columns), ['first_name', 'last_name', 'sex', 'dob', 'node', 'colors']);

    // Check getElementOptions for manually defined options.
    $this->assertEqual($yamlform->getElementOptions($inputs['colors']), [
      'red' => 'Red',
      'white' => 'White',
      'blue' => 'Blue',
    ]);

    // Check getElementOptions for token options pulled from YamlFormOptions.
    $this->assertEqual($yamlform->getElementOptions($inputs['sex']), [
      'Male' => 'Male',
      'Female' => 'Female',
    ]);

    // Set invalid inputs.
    $yamlform->set('inputs', "not\nvalid\nyaml")->save();

    // Check invalid inputs.
    $this->assertFalse($yamlform->getInputs());

    // Check invalid input columns.
    $this->assertEqual($yamlform->getElements(), []);

    // Check for 3 submissions..
    $this->assertEqual($this->submissionStorage->getTotal($yamlform), 3);

    // Check get options.
    $yes_no_options = ['Yes' => 'Yes', 'No' => 'No'];
    $this->assertEqual($yamlform->getElementOptions(['#options' => $yes_no_options]), $yes_no_options);
    $this->assertEqual($yamlform->getElementOptions(['#options' => 'yes_no']), $yes_no_options);
    $this->assertEqual($yamlform->getElementOptions(['#options' => 'not-found']), []);

    // Check 'test' state defaults to NULL.
    $this->assertEqual($yamlform->getState('test'), NULL);

    // Check 'test' state set to 123.
    $yamlform->setState('test', '123');
    $this->assertEqual($yamlform->getState('test'), '123');

    // Check YAML form  state namespacing.
    $this->assertEqual(\Drupal::state()->get('yamlform.' . $yamlform->id()), ['test' => '123']);

    // Check delete 'test' state is set back to NULL.
    $yamlform->deleteState('test');
    $this->assertEqual($yamlform->getState('test'), NULL);

    // Add 'test' state to YAML form so that we can confirm that is deleted.
    $yamlform->setState('test', '123');
    $this->assertEqual($yamlform->getState('test'), '123');

    // Check delete.
    $yamlform->delete();

    // Check all 3 submissions deleted.
    $this->assertEqual($this->submissionStorage->getTotal($yamlform), 0);

    // Check that 'test' state was deleted with the YAML form.
    $this->assertEqual(\Drupal::state()->get('yamlform.' . $yamlform->id()), NULL);
  }

}
