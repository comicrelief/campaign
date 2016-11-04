<?php

namespace Drupal\ds\Tests;

use Drupal\views\Tests\ViewTestData;
use Drupal\views\ViewExecutable;
use Drupal\Component\Utility\Unicode;
use Drupal\ds_test\Plugin\Block\DsTestBlock;

/**
 * Tests for managing custom code, and block fields.
 *
 * @group ds
 */
class BlockFieldPluginTest extends FastTestBase {

  /**
   * Modules to install.
   *
   * @var array
   */
  public static $modules = array(
    'node',
    'block',
    'ds',
    'ds_test',
    'layout_plugin',
    'views',
  );

  /**
   * Views used by this test.
   *
   * @var array
   *   The list of views.
   */
  public static $testViews = array('ds-testing');

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    // Ensure that the plugin definitions are cleared.
    foreach (ViewExecutable::getPluginTypes() as $plugin_type) {
      $this->container->get("plugin.manager.views.$plugin_type")->clearCachedDefinitions();
    }

    ViewTestData::createTestViews(get_class($this), array('ds_test'));
  }

  /**
   * Test  block title override.
   */
  public function testBlockFieldTitleOverride() {
    // Block fields.
    $edit = array(
      'name' => 'Test block title field',
      'id' => 'test_block_title_field',
      'entities[node]' => '1',
      'block' => 'views_block:ds_testing-block_1',
    );

    $this->dsCreateBlockField($edit);

    $this->dsSelectLayout();

    // Assert it's found on the Field UI for article.
    $this->drupalGet('admin/structure/types/manage/article/display');
    $this->assertRaw('fields[dynamic_block_field:node-test_block_title_field][weight]', t('Test block field found on node article.'));

    $fields = array(
      'fields[dynamic_block_field:node-test_block_title_field][region]' => 'left',
      'fields[dynamic_block_field:node-test_block_title_field][label]' => 'above',
      'fields[body][region]' => 'right',
    );

    $this->dsSelectLayout();
    $this->dsConfigureUi($fields);

    // Create a node.
    $settings = array('type' => 'article', 'promote' => 1);
    $node = $this->drupalCreateNode($settings);

    // Look at node and verify the block title is overridden.
    $this->drupalGet('node/' . $node->id());
    $this->assertRaw('Test block title field', t('Default field label.'));

    // Update testing label.
    $edit = array(
      'use_block_title' => '1',
    );
    $this->drupalPostForm('admin/structure/ds/fields/manage_block/test_block_title_field', $edit, t('Save'));
    $this->assertText(t('The field Test block title field has been saved'), t('Test field label override updated'));

    // Look at node and verify the block title is overridden.
    $this->drupalGet('node/' . $node->id());
    $this->assertRaw('Block title from view', t('Field label from view block display.'));
  }

  /**
   * Ensure block is not rendered if block disallows access.
   */
  function testBlockAccess() {
    $block_field_id = Unicode::strtolower($this->randomMachineName());
    $entity_type = 'node';

    $edit = [
      'name' => $this->randomString(),
      'id' => $block_field_id,
      'entities[' . $entity_type . ']' => TRUE,
      'block' => 'ds_test_block',
    ];
    $this->dsCreateBlockField($edit);

    $fields['fields[dynamic_block_field:' . $entity_type . '-' . $block_field_id . '][region]'] = 'left';
    $this->dsSelectLayout();
    $this->dsConfigureUI($fields);

    $settings['type'] = 'article';
    $node = $this->drupalCreateNode($settings);

    // Check block is not visible.
    \Drupal::state()->set('ds_test_block__access', FALSE);
    $this->drupalGet($node->toUrl());
    $this->assertNoRaw(DsTestBlock::BODY_TEXT);

    // Reset page cache.
    $this->resetAll();

    // Check block is visible.
    \Drupal::state()->set('ds_test_block__access', TRUE);
    $this->drupalGet($node->toUrl());
    $this->assertRaw(DsTestBlock::BODY_TEXT);
  }

}
