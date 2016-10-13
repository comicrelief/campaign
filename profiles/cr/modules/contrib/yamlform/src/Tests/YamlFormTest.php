<?php

namespace Drupal\yamlform\Tests;

/**
 * Tests for form entity.
 *
 * @group YamlForm
 */
class YamlFormTest extends YamlFormTestBase {

  /**
   * Tests form entity.
   */
  public function testYamlForm() {
    /** @var \Drupal\yamlform\YamlFormInterface $yamlform */
    list($yamlform) = $this->createYamlFormWithSubmissions();

    // Check get elements.
    $elements = $yamlform->getElementsInitialized();
    $this->assert(is_array($elements));

    // Check getElements.
    $columns = $yamlform->getElementsFlattenedAndHasValue();
    $this->assertEqual(array_keys($columns), ['first_name', 'last_name', 'sex', 'dob', 'node', 'colors', 'likert', 'address']);

    // Set invalid elements.
    $yamlform->set('elements', "not\nvalid\nyaml")->save();

    // Check invalid elements.
    $this->assertFalse($yamlform->getElementsInitialized());

    // Check invalid element columns.
    $this->assertEqual($yamlform->getElementsFlattenedAndHasValue(), []);

    // Check for 3 submissions..
    $this->assertEqual($this->submissionStorage->getTotal($yamlform), 3);

    // Check delete.
    $yamlform->delete();

    // Check all 3 submissions deleted.
    $this->assertEqual($this->submissionStorage->getTotal($yamlform), 0);

    // Check that 'test' state was deleted with the form.
    $this->assertEqual(\Drupal::state()->get('yamlform.' . $yamlform->id()), NULL);
  }

}
