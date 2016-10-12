<?php

namespace Drupal\yamlform\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Tests for form element options.
 *
 * @group YamlForm
 */
class YamlFormElementOptionsTest extends WebTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['system', 'user', 'yamlform', 'yamlform_test'];

  /**
   * Tests building of options elements.
   */
  public function test() {
    global $base_path;

    /**************************************************************************/
    // Processing.
    /**************************************************************************/

    // Check default value handling.
    $this->drupalPostForm('yamlform/test_element_options', [], t('Submit'));
    $this->assertRaw("yamlform_options: {  }
yamlform_options_default_value:
  one: One
  two: Two
  three: Three
yamlform_options_optgroup:
  'Group One':
    one: One
  'Group Two':
    two: Two
  'Group Three':
    three: Three
yamlform_element_options_entity: yes_no
yamlform_element_options_custom:
  one: One
  two: Two
  three: Three");

    // Check default value handling.
    $this->drupalPostForm('yamlform/test_element_options', ['yamlform_element_options_custom[options]' => 'yes_no'], t('Submit'));
    $this->assertRaw("yamlform_element_options_custom: yes_no");

    /**************************************************************************/
    // Rendering.
    /**************************************************************************/

    $this->drupalGet('yamlform/test_element_options');

    // Check empty 'yamlform_options' table and first tr.
    $this->assertRaw('<label for="edit-yamlform-options">yamlform_options</label>');
    $this->assertRaw('<div id="yamlform_options_table" class="yamlform-options-table"><table data-drupal-selector="edit-yamlform-options-options" id="edit-yamlform-options-options" class="responsive-enabled" data-striping="1">');
    $this->assertRaw('<tr class="draggable odd" data-drupal-selector="edit-yamlform-options-options-0">');
    $this->assertRaw('<input data-drupal-selector="edit-yamlform-options-options-0-value" type="text" id="edit-yamlform-options-options-0-value" name="yamlform_options[options][0][value]" value="" size="25" maxlength="128" placeholder="Enter value" class="form-text" />');
    $this->assertRaw('<td><div class="js-form-item form-item js-form-type-textfield form-type-textfield js-form-item-yamlform-options-options-0-text form-item-yamlform-options-options-0-text form-no-label">');
    $this->assertRaw('<input data-drupal-selector="edit-yamlform-options-options-0-text" type="text" id="edit-yamlform-options-options-0-text" name="yamlform_options[options][0][text]" value="" size="25" placeholder="Enter text" class="form-text" />');
    $this->assertRaw('<input class="yamlform-options-sort-weight form-number" data-drupal-selector="edit-yamlform-options-options-0-weight" type="number" id="edit-yamlform-options-options-0-weight" name="yamlform_options[options][0][weight]" value="0" step="1" size="10" />');
    $this->assertRaw('<td><input data-drupal-selector="edit-yamlform-options-options-0-remove" formnovalidate="formnovalidate" type="image" id="edit-yamlform-options-options-0-remove" name="yamlform_options_table_remove_0" src="' . $base_path . 'core/misc/icons/787878/ex.svg" class="image-button js-form-submit form-submit" />');

    // Check optgroup 'yamlform_options' display CodeMirror editor.
    $this->assertRaw('<label for="edit-yamlform-options-optgroup" class="js-form-required form-required">yamlform_options (optgroup)</label>');
    $this->assertRaw('<textarea data-drupal-selector="edit-yamlform-options-optgroup-options" aria-describedby="edit-yamlform-options-optgroup-options--description" class="js-yamlform-codemirror yamlform-codemirror yaml form-textarea resize-vertical" data-yamlform-codemirror-mode="text/x-yaml" id="edit-yamlform-options-optgroup-options" name="yamlform_options_optgroup[options]" rows="5" cols="60">');

    /**************************************************************************/
    // Processing.
    /**************************************************************************/

    // Check populated 'yamlform_options_default_value'.
    $this->assertFieldByName('yamlform_options_default_value[options][0][value]', 'one');
    $this->assertFieldByName('yamlform_options_default_value[options][0][text]', 'One');
    $this->assertFieldByName('yamlform_options_default_value[options][1][value]', 'two');
    $this->assertFieldByName('yamlform_options_default_value[options][1][text]', 'Two');
    $this->assertFieldByName('yamlform_options_default_value[options][2][value]', 'three');
    $this->assertFieldByName('yamlform_options_default_value[options][2][text]', 'Three');
    $this->assertFieldByName('yamlform_options_default_value[options][3][value]', '');
    $this->assertFieldByName('yamlform_options_default_value[options][3][text]', '');
    $this->assertNoFieldByName('yamlform_options_default_value[options][4][value]', '');
    $this->assertNoFieldByName('yamlform_options_default_value[options][4][text]', '');

    // Check adding 'four' and 1 more option.
    $edit = [
      'yamlform_options_default_value[options][3][value]' => 'four',
      'yamlform_options_default_value[options][3][text]' => 'Four',
    ];
    $this->drupalPostAjaxForm(NULL, $edit, 'yamlform_options_default_value_table_add');
    $this->assertFieldByName('yamlform_options_default_value[options][3][value]', 'four');
    $this->assertFieldByName('yamlform_options_default_value[options][3][text]', 'Four');
    $this->assertFieldByName('yamlform_options_default_value[options][4][value]', '');
    $this->assertFieldByName('yamlform_options_default_value[options][4][text]', '');

    // Check add 10 more rows.
    $edit = ['yamlform_options_default_value[add][more_options]' => 10];
    $this->drupalPostAjaxForm(NULL, $edit, 'yamlform_options_default_value_table_add');
    $this->assertFieldByName('yamlform_options_default_value[options][14][value]', '');
    $this->assertFieldByName('yamlform_options_default_value[options][14][text]', '');
    $this->assertNoFieldByName('yamlform_options_default_value[options][15][value]', '');
    $this->assertNoFieldByName('yamlform_options_default_value[options][15][text]', '');

    // Check remove 'one' options.
    $this->drupalPostAjaxForm(NULL, $edit, 'yamlform_options_default_value_table_remove_0');
    $this->assertNoFieldByName('yamlform_options_default_value[options][14][value]', '');
    $this->assertNoFieldByName('yamlform_options_default_value[options][14][text]', '');
    $this->assertNoFieldByName('yamlform_options_default_value[options][0][value]', 'one');
    $this->assertNoFieldByName('yamlform_options_default_value[options][0][text]', 'One');
    $this->assertFieldByName('yamlform_options_default_value[options][0][value]', 'two');
    $this->assertFieldByName('yamlform_options_default_value[options][0][text]', 'Two');
  }

}
