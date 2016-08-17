<?php

namespace Drupal\paragraphs\Tests;

use Drupal\field_ui\Tests\FieldUiTestTrait;

/**
 * Tests paragraphs add modes.
 *
 * @group paragraphs
 */
class ParagraphsAddModesTest extends ParagraphsTestBase {

  use FieldUiTestTrait;

  /**
   * Tests that paragraphs field does not allow default values.
   */
  public function testNoDefaultValue() {
    $this->loginAsAdmin();
    $this->addParagraphedContentType('paragraphed_test', 'paragraphs_field');

    // Edit the field.
    $this->drupalGet('admin/structure/types/manage/paragraphed_test/fields');
    $this->clickLink(t('Edit'));

    // Check that the current field does not allow to add default values.
    $this->assertText('No widget available for: paragraphs_field.');
    $this->drupalPostForm(NULL, [], t('Save settings'));
    $this->assertText('Saved paragraphs_field configuration.');
    $this->assertResponse(200);
  }

  /**
   * Tests the field creation when no paragraphs types are available.
   */
  public function testEmptyAllowedTypes() {
    $this->loginAsAdmin();
    $this->addParagraphedContentType('paragraphed_test', 'paragraphs');

    // Edit the field and save when there are no paragraphs types available.
    $this->drupalGet('admin/structure/types/manage/paragraphed_test/fields');
    $this->clickLink(t('Edit'));
    $this->drupalPostForm(NULL, [], t('Save settings'));
    $this->assertText('Saved paragraphs configuration.');
  }

  /**
   * Tests the add drop down button.
   */
  public function testDropDownMode() {
    $this->loginAsAdmin();
    // Add two paragraph types.
    $this->addParagraphsType('btext');
    $this->addParagraphsType('dtext');

    $this->addParagraphedContentType('paragraphed_test', 'paragraphs');
    // Enter to the field config since the weight is set through the form.
    $this->drupalGet('admin/structure/types/manage/paragraphed_test/fields/node.paragraphed_test.paragraphs');
    $this->drupalPostForm(NULL, [], 'Save settings');

    $this->setAddMode('paragraphed_test', 'paragraphs', 'dropdown');

    $this->assertAddButtons(['Add btext', 'Add dtext']);

    $this->addParagraphsType('atext');
    $this->assertAddButtons(['Add btext', 'Add dtext', 'Add atext']);

    $this->setParagraphsTypeWeight('paragraphed_test', 'dtext', 2, 'paragraphs');
    $this->assertAddButtons(['Add dtext', 'Add btext', 'Add atext']);

    $this->setAllowedParagraphsTypes('paragraphed_test', ['dtext', 'atext'], TRUE, 'paragraphs');
    $this->assertAddButtons(['Add dtext', 'Add atext']);

    $this->setParagraphsTypeWeight('paragraphed_test', 'atext', 1, 'paragraphs');
    $this->assertAddButtons(['Add atext', 'Add dtext']);

    $this->setAllowedParagraphsTypes('paragraphed_test', ['atext', 'dtext', 'btext'], TRUE, 'paragraphs');
    $this->assertAddButtons(['Add atext', 'Add dtext', 'Add btext']);
  }

  /**
   * Tests the add select mode.
   */
  public function testSelectMode() {
    $this->loginAsAdmin();
    // Add two paragraph types.
    $this->addParagraphsType('btext');
    $this->addParagraphsType('dtext');

    $this->addParagraphedContentType('paragraphed_test', 'paragraphs');
    // Enter to the field config since the weight is set through the form.
    $this->drupalGet('admin/structure/types/manage/paragraphed_test/fields/node.paragraphed_test.paragraphs');
    $this->drupalPostForm(NULL, [], 'Save settings');

    $this->setAddMode('paragraphed_test', 'paragraphs', 'select');

    $this->assertSelectOptions(['btext', 'dtext'], 'paragraphs');

    $this->addParagraphsType('atext');
    $this->assertSelectOptions(['btext', 'dtext', 'atext'], 'paragraphs');

    $this->setParagraphsTypeWeight('paragraphed_test', 'dtext', 2, 'paragraphs');
    $this->assertSelectOptions(['dtext', 'btext', 'atext'], 'paragraphs');

    $this->setAllowedParagraphsTypes('paragraphed_test', ['dtext', 'atext'], TRUE, 'paragraphs');
    $this->assertSelectOptions(['dtext', 'atext'], 'paragraphs');

    $this->setParagraphsTypeWeight('paragraphed_test', 'atext', 1, 'paragraphs');
    $this->assertSelectOptions(['atext', 'dtext'], 'paragraphs');

    $this->setAllowedParagraphsTypes('paragraphed_test', ['atext', 'dtext', 'btext'], TRUE, 'paragraphs');
    $this->assertSelectOptions(['atext', 'dtext', 'btext'], 'paragraphs');
  }

  /**
   * Asserts order and quantity of add buttons.
   *
   * @param array $options
   *   Array of expected add buttons in its correct order.
   */
  protected function assertAddButtons($options) {
    $this->drupalGet('node/add/paragraphed_test');
    $buttons = $this->xpath('//input[@class="field-add-more-submit button js-form-submit form-submit"]');
    // Check if the buttons are in the same order as the given array.
    foreach ($buttons as $key => $button) {
      $this->assertEqual($button['value'], $options[$key]);
    }
    $this->assertTrue(count($buttons) == count($options), 'The amount of drop down options matches with the given array');
  }

  /**
   * Asserts order and quantity of select add options.
   *
   * @param array $options
   *   Array of expected select options in its correct order.
   * @param string $paragraphs_field
   *   Name of the paragraphs field to check.
   */
  protected function assertSelectOptions($options, $paragraphs_field) {
    $this->drupalGet('node/add/paragraphed_test');
    $buttons = $this->xpath('//*[@name="' . $paragraphs_field . '[add_more][add_more_select]"]/option');
    // Check if the options are in the same order as the given array.
    foreach ($buttons as $key => $button) {
      $this->assertEqual($button['value'], $options[$key]);
    }
    $this->assertTrue(count($buttons) == count($options), 'The amount of select options matches with the given array');
    $this->assertNotEqual($this->xpath('//*[@name="' . $paragraphs_field .'_add_more"]'), [], 'The add button is displayed');
  }
}
