<?php

namespace Drupal\ds\Tests;

/**
 * Tests for testing field permissions.
 *
 * @group ds
 */
class FieldPermissionsTest extends FastTestBase {

  /**
   * Modules to install.
   *
   * @var array
   */
  public static $modules = array(
    'node',
    'field_ui',
    'taxonomy',
    'block',
    'ds',
    'ds_extras',
    'ds_test',
    'layout_plugin',
    'views',
    'views_ui',
  );

  /**
   * Tests field permissions.
   */
  public function testFieldPermissions() {

    $fields = array(
      'fields[body][region]' => 'right',
      'fields[test_field][region]' => 'left',
    );

    $this->config('ds_extras.settings')->set('field_permissions', TRUE)->save();
    \Drupal::moduleHandler()->resetImplementations();

    $this->dsSelectLayout();
    $this->dsConfigureUi($fields);

    // Create a node.
    $settings = array('type' => 'article');
    $node = $this->drupalCreateNode($settings);
    $this->drupalGet('node/' . $node->id());
    $this->assertRaw('group-right', 'Template found (region right)');
    $this->assertNoText('Test field plugin on node ' . $node->id(), 'Test code field not found');

    // Give permissions.
    $edit = array(
      'authenticated[view node_author on node]' => 1,
      'authenticated[view test_field on node]' => 1,
    );
    $this->drupalPostForm('admin/people/permissions', $edit, t('Save permissions'));
    $this->drupalGet('node/' . $node->id());
    $this->assertRaw('group-left', 'Template found (region left)');
    $this->assertText('Test field plugin on node ' . $node->id(), 'Test field plugin found');
  }

}
