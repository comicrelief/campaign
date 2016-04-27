<?php

/**
 * @file
 * Contains \Drupal\filter\Tests\FilterSecurityTest.
 */

namespace Drupal\filter\Tests;

use Drupal\simpletest\WebTestBase;
use Drupal\filter\Plugin\FilterInterface;
use Drupal\user\RoleInterface;

/**
 * Tests the behavior of check_markup() when a filter or text format vanishes,
 * or when check_markup() is called in such a way that it is instructed to skip
 * all filters of the "FilterInterface::TYPE_HTML_RESTRICTOR" type.
 *
 * @group filter
 */
class FilterSecurityTest extends WebTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('node', 'filter_test');

  /**
   * A user with administrative permissions.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $adminUser;

  protected function setUp() {
    parent::setUp();

    // Create Basic page node type.
    $this->drupalCreateContentType(array('type' => 'page', 'name' => 'Basic page'));

    /** @var \Drupal\filter\Entity\FilterFormat $filtered_html_format */
    $filtered_html_format = entity_load('filter_format', 'filtered_html');
    $filtered_html_permission = $filtered_html_format->getPermissionName();
    user_role_grant_permissions(RoleInterface::ANONYMOUS_ID, array($filtered_html_permission));

    $this->adminUser = $this->drupalCreateUser(array('administer modules', 'administer filters', 'administer site configuration'));
    $this->drupalLogin($this->adminUser);
  }

  /**
   * Tests removal of filtered content when an active filter is disabled.
   *
   * Tests that filtered content is emptied when an actively used filter module
   * is disabled.
   */
  function testDisableFilterModule() {
    // Create a new node.
    $node = $this->drupalCreateNode(array('promote' => 1));
    $body_raw = $node->body->value;
    $format_id = $node->body->format;
    $this->drupalGet('node/' . $node->id());
    $this->assertText($body_raw, 'Node body found.');

    // Enable the filter_test_replace filter.
    $edit = array(
      'filters[filter_test_replace][status]' => 1,
    );
    $this->drupalPostForm('admin/config/content/formats/manage/' . $format_id, $edit, t('Save configuration'));

    // Verify that filter_test_replace filter replaced the content.
    $this->drupalGet('node/' . $node->id());
    $this->assertNoText($body_raw, 'Node body not found.');
    $this->assertText('Filter: Testing filter', 'Testing filter output found.');

    // Disable the text format entirely.
    $this->drupalPostForm('admin/config/content/formats/manage/' . $format_id . '/disable', array(), t('Disable'));

    // Verify that the content is empty, because the text format does not exist.
    $this->drupalGet('node/' . $node->id());
    $this->assertNoText($body_raw, 'Node body not found.');
  }

  /**
   * Tests that security filters are enforced even when marked to be skipped.
   */
  function testSkipSecurityFilters() {
    $text = "Text with some disallowed tags: <script />, <p><object>unicorn</object></p>, <i><table></i>.";
    $expected_filtered_text = "Text with some disallowed tags: , <p>unicorn</p>, .";
    $this->assertEqual(check_markup($text, 'filtered_html', '', array()), $expected_filtered_text, 'Expected filter result.');
    $this->assertEqual(check_markup($text, 'filtered_html', '', array(FilterInterface::TYPE_HTML_RESTRICTOR)), $expected_filtered_text, 'Expected filter result, even when trying to disable filters of the FilterInterface::TYPE_HTML_RESTRICTOR type.');
  }
}
