<?php

/**
 * @file
 * Contains \Drupal\inline_entity_form\Tests\InlineEntityFormWebTest.
 */

namespace Drupal\inline_entity_form\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Tests the IEF element on a custom form.
 *
 * @group inline_entity_form
 */
class InlineEntityFormWebTest extends WebTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['inline_entity_form_test'];

  /**
   * User with permissions to create content.
   *
   * @var \Drupal\user\Entity\User
   */
  protected $user;
  /**
   * Prepares environment for
   */
  protected function setUp() {
    parent::setUp();

    $this->user = $this->createUser([
      'create ief_simple_single content',
      'edit any ief_simple_single content',
      'edit any ief_test_custom content',
      'view own unpublished content',
    ]);
  }

  /**
   * Tests IEF on a custom form.
   */
  public function testCustomFormIEF() {
    $this->drupalGet('ief-test');
    $this->assertText(t('Title'), 'Title field found on the form.');

    $edit = ['inline_entity_form[title][0][value]' => $this->randomString()];
    $this->drupalPostForm('ief-test', $edit, t('Save'));
    $message = t('Created @entity_type @label.', ['@entity_type' => t('Content'), '@label' => $edit['inline_entity_form[title][0][value]']]);
    $this->assertText($message, 'Status message found on the page.');

    /** @var \Drupal\node\NodeInterface $node */
    $node = $this->container->get('entity.manager')->getStorage('node')->load(1);
    $this->assertEqual($node->label(), $edit['inline_entity_form[title][0][value]'], 'Node title correctly saved to the database.');
    $this->assertEqual($node->bundle(), 'ief_test_custom', 'Correct bundle used when creating the new node.');
  }

  /**
   * Tests simple IEF widget with single-value field.
   */
  public function testSimpleSingle() {
    $this->drupalLogin($this->user);
    $this->drupalGet('node/add/ief_simple_single');

    $this->assertText('Single node', 'Inline entity field widget title found.');
    $this->assertText('Reference a single node.', 'Inline entity field description found.');

    $edit = [
      'title[0][value]' => 'Host node',
      'single[0][inline_entity_form][title][0][value]' => 'Child node',
    ];
    $this->drupalPostForm('node/add/ief_simple_single', $edit, t('Save'));
  }

}
