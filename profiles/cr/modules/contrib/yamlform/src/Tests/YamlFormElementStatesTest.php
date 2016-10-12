<?php

namespace Drupal\yamlform\Tests;

use Drupal\Core\Form\OptGroup;
use Drupal\simpletest\WebTestBase;
use Drupal\yamlform\Entity\YamlForm;

/**
 * Tests for form element #states.
 *
 * @group YamlForm
 */
class YamlFormElementStatesTest extends WebTestBase {

  use YamlFormTestTrait;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['system', 'filter', 'file', 'language', 'node', 'user', 'yamlform', 'yamlform_examples', 'yamlform_test'];

  /**
   * Tests element #states selectors.
   */
  public function testSelectors() {
    /** @var \Drupal\yamlform\YamlFormInterface $yamlform */
    $yamlform = YamlForm::load('example_elements');
    $yamlform->setStatus(TRUE)->save();

    $this->drupalGet('yamlform/example_elements');

    $selectors = OptGroup::flattenOptions($yamlform->getElementsSelectorOptions());
    // Ignore text format and captcha selectors which are not available during
    // this test.
    unset(
      $selectors[':input[name="text_format[format]"]'],
      $selectors[':input[name="captcha"]']
    );
    foreach ($selectors as $selector => $name) {
      // Remove :input since it is a jQuery specific selector.
      $selector = str_replace(':input', '', $selector);
      $this->assertCssSelect($selector);
    }
  }

  /**
   * Tests element #states.
   */
  public function testElement() {

    /**************************************************************************/
    // Processing.
    /**************************************************************************/

    // Check default value handling.
    $this->drupalPostForm('yamlform/test_element_states', [], t('Submit'));
    $this->assertRaw("states_basic:
  enabled:
    selector_01:
      checked: true
  required:
    - 'selector_01''':
        checked: true
    - selector_02:
        checked: true
  disabled:
    - selector_01:
        checked: true
    - or
    - selector_02:
        checked: true
states_custom_selector:
  required:
    custom_selector:
      value: 'Yes'
states_empty: {  }
states_unsupported_operator:
  required:
    - custom_selector:
        value: 'Yes'
    - xor
    - custom_selector:
        value: 'Yes'
states_unsupported_nesting:
  required:
    - selector_01:
        value: 'Yes'
      selector_02:
        value: 'Yes'
    - or
    - selector_03:
        value: 'Yes'
      selector_04:
        value: 'Yes'");

    /**************************************************************************/
    // Rendering.
    /**************************************************************************/

    $this->drupalGet('yamlform/test_element_states');

    // Check 'States custom selector'.
    $this->assertRaw('<input data-drupal-selector="edit-states-custom-selector-states-1-selector-other" type="text" id="edit-states-custom-selector-states-1-selector-other" name="states_custom_selector[states][1][selector][other]" value="custom_selector" size="60" maxlength="128" placeholder="Enter other..." class="form-text" />');

    // Check 'States unsupport operator'.
    $this->assertRaw('Conditional logic (Form API #states) is using the <em class="placeholder">XOR</em> operator. Form API #states must be manually entered.');
    $this->assertRaw('<textarea data-drupal-selector="edit-states-unsupported-operator-states" aria-describedby="edit-states-unsupported-operator-states--description" class="js-yamlform-codemirror yamlform-codemirror yaml form-textarea resize-vertical" data-yamlform-codemirror-mode="text/x-yaml" id="edit-states-unsupported-operator-states" name="states_unsupported_operator[states]" rows="5" cols="60">');

    // Check 'States unsupport nested multiple selectors'.
    $this->assertRaw('Conditional logic (Form API #states) is using multiple nested conditions. Form API #states must be manually entered.');
    $this->assertRaw('<textarea data-drupal-selector="edit-states-unsupported-nesting-states" aria-describedby="edit-states-unsupported-nesting-states--description" class="js-yamlform-codemirror yamlform-codemirror yaml form-textarea resize-vertical" data-yamlform-codemirror-mode="text/x-yaml" id="edit-states-unsupported-nesting-states" name="states_unsupported_nesting[states]" rows="5" cols="60">');

    /**************************************************************************/
    // Processing.
    /**************************************************************************/

    // Check setting first state and adding new state.
    $edit = [
      'states_empty[states][0][state]' => 'required',
      'states_empty[states][1][selector][select]' => 'selector_01',
      'states_empty[states][1][selector][other]' => '',
      'states_empty[states][1][trigger]' => 'value',
      'states_empty[states][1][value]' => '{value_01}',
    ];
    $this->drupalPostAjaxForm(NULL, $edit, 'states_empty_table_add');

    // Check the first state/condition is required and value = {value_01}.
    $this->assertFieldByName('states_empty[states][0][state]', 'required');
    $this->assertFieldByName('states_empty[states][1][selector][select]', 'selector_01');
    $this->assertFieldByName('states_empty[states][1][trigger]', 'value');
    $this->assertFieldByName('states_empty[states][1][value]', '{value_01}');

    // Check empty second state/condition.
    $this->assertFieldByName('states_empty[states][2][state]', '');
    $this->assertFieldByName('states_empty[states][3][selector][select]', '');
    $this->assertFieldByName('states_empty[states][3][trigger]', '');
    $this->assertFieldByName('states_empty[states][3][value]', '');

    $edit = [
      'states_empty[states][2][state]' => 'disabled',
      'states_empty[states][3][selector][select]' => 'selector_02',
      'states_empty[states][3][selector][other]' => '',
      'states_empty[states][3][trigger]' => 'value',
      'states_empty[states][3][value]' => '{value_02}',
    ];
    $this->drupalPostAjaxForm(NULL, $edit, 'states_empty_table_remove_1');

    // Check the first condition is removed.
    $this->assertNoFieldByName('states_empty[states][1][selector][select]', 'selector_01');
    $this->assertNoFieldByName('states_empty[states][1][trigger]', 'value');
    $this->assertNoFieldByName('states_empty[states][1][value]', '{value_01}');

    // Check the second state/condition is required and value = {value_01}.
    $this->assertFieldByName('states_empty[states][1][state]', 'disabled');
    $this->assertFieldByName('states_empty[states][2][selector][select]', 'selector_02');
    $this->assertFieldByName('states_empty[states][2][trigger]', 'value');
    $this->assertFieldByName('states_empty[states][2][value]', '{value_02}');

    // Remove state two.
    $this->drupalPostAjaxForm(NULL, [], 'states_empty_table_remove_1');

    // Check the second state/condition is removed.
    $this->assertNoFieldByName('states_empty[states][1][state]', 'disabled');
    $this->assertNoFieldByName('states_empty[states][2][selector][select]', 'selector_02');
    $this->assertNoFieldByName('states_empty[states][2][trigger]', 'value');
    $this->assertNoFieldByName('states_empty[states][2][value]', '{value_02}');
  }

}
