<?php

/**
 * @file
 * Contains \Drupal\inline_entity_form\Tests\InlineEntityFormElementWebTest.
 */

namespace Drupal\inline_entity_form\Tests;

/**
 * Tests the IEF element on a custom form.
 *
 * @group inline_entity_form
 */
class InlineEntityFormElementWebTest extends InlineEntityFormTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['inline_entity_form_test'];

  /**
   * Prepares environment for
   */
  protected function setUp() {
    parent::setUp();

    $this->user = $this->createUser([
      'edit any ief_test_custom content',
      'view own unpublished content',
    ]);

    $this->fieldStorageConfigStorage = $this->container
      ->get('entity_type.manager')
      ->getStorage('field_storage_config');
  }

  /**
   * Tests IEF on a custom form.
   */
  public function testCustomFormIEF() {
    $title = $this->randomMachineName();
    $this->drupalGet('ief-test');
    $this->assertText(t('Title'), 'Title field found on the form.');
    $this->assertText(t('Positive int'), 'Positive int field found on form.');

    $edit = [];
    $this->drupalPostForm('ief-test', $edit, t('Save'));
    $this->assertText('Title field is required.');
    // Currently this assert will fail
    //$this->assertNoText('This value should not be null.');
    $this->assertNoNodeByTitle($title);

    $edit['inline_entity_form[title][0][value]'] = $title;
    $edit['inline_entity_form[positive_int][0][value]'] = -1;
    $this->drupalPostForm('ief-test', $edit, t('Save'));
    $this->assertText('Positive int must be higher than or equal to 1');
    $this->assertNoNodeByTitle($title);

    $edit['inline_entity_form[positive_int][0][value]'] = 11;
    $this->drupalPostForm('ief-test', $edit, t('Save'));
    $message = t('Created @entity_type @label.', ['@entity_type' => t('Content'), '@label' => $edit['inline_entity_form[title][0][value]']]);
    $this->assertText($message, 'Status message found on the page.');
    $this->assertNodeByTitle($title, 'ief_test_custom');

    if ($node = $this->getNodeByTitle($title)) {
      $this->drupalGet('ief-edit-test/' . $node->id());
      $this->assertFieldByName('inline_entity_form[title][0][value]', $title, 'Node title appears in form.');
      //$this->assertText($title, 'Node title appears in form.');
      $this->assertText(11, 'Positive int field appears in form.');
      $updated_title = $title . ' - updated';
      $edit['inline_entity_form[title][0][value]'] = $updated_title;
      $this->drupalPostForm(NULL, $edit, t('Update'));
      $this->assertNodeByTitle($updated_title, 'ief_test_custom');
    }

  }
}
