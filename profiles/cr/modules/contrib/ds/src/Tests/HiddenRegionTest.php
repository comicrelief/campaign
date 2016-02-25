<?php

/**
 * @file
 * Definition of Drupal\ds\Tests\HiddenRegionTest.
 */

namespace Drupal\ds\Tests;

/**
 * Tests for testing the hidden region option.
 *
 * @group ds
 */
class HiddenRegionTest extends FastTestBase {

  function testHiddenRegion() {
    // Enable the hidden region option
    $edit = array(
      'fs3[hidden_region]' => TRUE
    );
    $this->drupalPostForm('admin/structure/ds/settings', $edit, t('Save configuration'));

    $this->dsSelectLayout();

    // Create a node.
    $settings = array('type' => 'article');
    $node = $this->drupalCreateNode($settings);

    // Configure fields
    $fields = array(
      'fields[body][region]' => 'right',
      'fields[test_field][region]' => 'ds_hidden',
    );
    $this->dsConfigureUI($fields);

    // Test field not printed
    $this->drupalGet('node/' . $node->id());
    $this->assertNoText('Test field plugin on node ' . $node->id(), 'Test code field not found');

    // Configure fields
    $fields = array(
      'fields[body][region]' => 'right',
      'fields[test_field][region]' => 'right',
    );
    $this->dsConfigureUI($fields);

    // Test field printed
    $this->drupalGet('node/' . $node->id());
    $this->assertText('Test field plugin on node ' . $node->id(), 'Test code field not found');
  }
}
