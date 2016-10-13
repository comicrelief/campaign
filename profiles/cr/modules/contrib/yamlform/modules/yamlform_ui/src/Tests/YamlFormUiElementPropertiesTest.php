<?php

namespace Drupal\yamlform_ui\Tests;

use Drupal\yamlform\Tests\YamlFormTestBase;
use Drupal\yamlform\Entity\YamlForm;

/**
 * Tests for form UI element properties.
 *
 * @group YamlFormUi
 */
class YamlFormUiElementPropertiesTest extends YamlFormTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['system', 'filter', 'user', 'yamlform', 'yamlform_test', 'yamlform_examples', 'yamlform_ui'];

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->createFilters();
  }

  /**
   * Tests element properties.
   */
  public function testElementProperties() {
    $this->drupalLogin($this->adminFormUser);

    // Loops through all the elements, edits them via the UI, and check that
    // the element's render array has not be altered.
    // This verifies that the edit element form is not unexpectedly altering
    // an element's render array.
    $yamlform_ids = ['example_layout_basic', 'test_element', 'test_element_extras', 'test_form_states_triggers'];
    foreach ($yamlform_ids as $yamlform_id) {
      /** @var \Drupal\yamlform\YamlFormInterface $yamlform_elements */
      $yamlform_elements = YamlForm::load($yamlform_id);
      $original_elements = $yamlform_elements->getElementsDecodedAndFlattened();
      foreach ($original_elements as $key => $original_element) {
        $this->drupalPostForm('admin/structure/yamlform/manage/' . $yamlform_elements->id() . '/element/' . $key . '/edit', [], t('Save'));

        // Must reset the form entity cache so that the update elements can
        // be loaded.
        \Drupal::entityTypeManager()->getStorage('yamlform_submission')->resetCache();

        /** @var \Drupal\yamlform\YamlFormInterface $yamlform_elements */
        $yamlform_elements = YamlForm::load($yamlform_id);
        $updated_element = $yamlform_elements->getElementsDecodedAndFlattened()[$key];

        $this->assertEqual($original_element, $updated_element, "'$key'' properties is equal.");
      }
    }
  }

}
