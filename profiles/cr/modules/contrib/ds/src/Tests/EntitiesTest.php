<?php

/**
 * @file
 * Contains \Drupal\ds\Tests\EntitiesTest.
 */

namespace Drupal\ds\Tests;

/**
 * Tests for display of nodes and fields.
 *
 * @group ds
 */
class EntitiesTest extends FastTestBase {

  /**
   * Modules to install.
   *
   * @var array
   */
  public static $modules = array('node', 'field_ui', 'taxonomy', 'block', 'ds', 'ds_test', 'layout_plugin', 'ds_switch_view_mode');

  /**
   * {@inheritdoc}
   */
  protected function setup() {
    parent::setup();

    // Enable field templates
    \Drupal::configFactory()->getEditable('ds.settings')
      ->set('field_template', TRUE)
      ->save();
  }

  /**
   * Test basic node display fields.
   */
  function testDSNodeEntity() {

    /** @var \Drupal\node\NodeInterface $node */
    $node = $this->entitiesTestSetup();

    // Test theme_hook_suggestions in ds_entity_variables().
    $this->drupalGet('node/' . $node->id(), array('query' => array('store_suggestions' => 1)));
    $cache = $this->container->get('cache.default')->get('ds_test_suggestions');
    $hook_suggestions = $cache->data;
    $expected_hook_suggestions = array(
      'ds_2col_stacked__node',
      'ds_2col_stacked__node_full',
      'ds_2col_stacked__node_article',
      'ds_2col_stacked__node_article_full',
      'ds_2col_stacked__node__1'
    );
    $this->assertEqual($hook_suggestions, $expected_hook_suggestions);

    // Look at node and verify token and block field.
    $this->drupalGet('node/' . $node->id());
    $this->assertRaw('view-mode-full', 'Template file found (in full view mode)');
    $this->assertRaw('<div class="field field--name-dynamic-token-fieldnode-token-field field--type-ds field--label-hidden field__item">', t('Token field found'));
    $xpath = $this->xpath('//div[@class="field field--name-dynamic-token-fieldnode-token-field field--type-ds field--label-hidden field__item"]');
    $this->assertEqual((string) $xpath[0]->p, $node->getTitle(), 'Token field content found');
    $this->assertRaw('group-header', 'Template found (region header)');
    $this->assertRaw('group-footer', 'Template found (region footer)');
    $this->assertRaw('group-left', 'Template found (region left)');
    $this->assertRaw('group-right', 'Template found (region right)');
    $this->assertRaw('<div class="field field--name-node-submitted-by field--type-ds field--label-hidden field__item">', 'Submitted by line found');
    $xpath = $this->xpath('//div[@class="field field--name-node-submitted-by field--type-ds field--label-hidden field__item"]');
    $this->assertText('Submitted by ' . (string) $xpath[0]->a->span . ' on', 'Submitted by line found');

    // Configure teaser layout.
    $teaser = array(
      'layout' => 'ds_2col',
    );
    $teaser_assert = array(
      'regions' => array(
        'left' => '<td colspan="8">' . t('Left') . '</td>',
        'right' => '<td colspan="8">' . t('Right') . '</td>',
      ),
    );
    $this->dsSelectLayout($teaser, $teaser_assert, 'admin/structure/types/manage/article/display/teaser');

    $fields = array(
      'fields[dynamic_token_field:node-token_field][region]' => 'left',
      'fields[body][region]' => 'right',
      'fields[node_links][region]' => 'right',
    );
    $this->dsConfigureUI($fields, 'admin/structure/types/manage/article/display/teaser');

    // Switch view mode on full node page.
    $edit = array('ds_switch' => 'teaser');
    $this->drupalPostForm('node/' . $node->id() . '/edit', $edit, t('Save and keep published'));
    $this->assertRaw('view-mode-teaser', 'Switched to teaser mode');
    $this->assertRaw('group-left', 'Template found (region left)');
    $this->assertRaw('group-right', 'Template found (region right)');
    $this->assertNoRaw('group-header', 'Template found (no region header)');
    $this->assertNoRaw('group-footer', 'Template found (no region footer)');

    $edit = array('ds_switch' => '');
    $this->drupalPostForm('node/' . $node->id() . '/edit', $edit, t('Save and keep published'));
    $this->assertRaw('view-mode-full', 'Switched to full mode again');

    // Test all options of a block field.
    $block = array(
      'name' => 'Test block field',
    );
    $this->dsCreateBlockField($block);
    $fields = array(
      'fields[dynamic_block_field:node-test_block_field][region]' => 'left',
      'fields[dynamic_token_field:node-token_field][region]' => 'hidden',
      'fields[body][region]' => 'hidden',
      'fields[node_links][region]' => 'hidden',
    );
    $this->dsConfigureUI($fields);
    $this->drupalGet('node/' . $node->id());
    $this->assertRaw('field--name-dynamic-block-fieldnode-test-block-field');

    // Test revisions. Enable the revision view mode
    $edit = array(
      'display_modes_custom[revision]' => '1'
    );
    $this->drupalPostForm('admin/structure/types/manage/article/display', $edit, t('Save'));

    // Enable the override revision mode and configure it
    $edit = array(
      'fs3[override_node_revision]' => TRUE,
      'fs3[override_node_revision_view_mode]' => 'revision'
    );
    $this->drupalPostForm('admin/structure/ds/settings', $edit, t('Save configuration'));

    // Select layout and configure fields.
    $edit = array(
      'layout' => 'ds_2col',
    );
    $assert = array(
      'regions' => array(
        'left' => '<td colspan="8">' . t('Left') . '</td>',
        'right' => '<td colspan="8">' . t('Right') . '</td>',
      ),
    );
    $this->dsSelectLayout($edit, $assert, 'admin/structure/types/manage/article/display/revision');
    $edit = array(
      'fields[body][region]' => 'left',
      'fields[node_link][region]' => 'right',
      'fields[node_author][region]' => 'right',
    );
    $this->dsConfigureUI($edit, 'admin/structure/types/manage/article/display/revision');

    // Create revision of the node.
    $edit = array(
      'revision' => TRUE,
      'revision_log[0][value]' => 'Test revision',
    );
    $this->drupalPostForm('node/' . $node->id() . '/edit', $edit, t('Save and keep published'));

    // Verify the revision is created
    $this->drupalGet('node/' . $node->id() . '/revisions');
    $this->assertText('Test revision');

    // Assert revision is using 2 col template.
    $this->drupalGet('node/' . $node->id() . '/revisions/1/view');
    $this->assertText('Body', 'Body label');

    // Assert full view is using stacked template.
    $this->drupalGet('node/' . $node->id());
    $this->assertNoText('Body', 'No Body label');

    // Test formatter limit on article with tags.
    $edit = array(
      'ds_switch' => '',
      'field_tags[0][target_id]' => 'Tag 1',
      'field_tags[1][target_id]' => 'Tag 2',
    );
    $this->drupalPostForm('node/' . $node->id() . '/edit', $edit, t('Save and keep published'));
    $edit = array(
      'fields[field_tags][region]' => 'right',
      'fields[field_tags][type]' => 'entity_reference_label',
    );
    $this->dsConfigureUI($edit, 'admin/structure/types/manage/article/display');
    $this->drupalGet('node/' . $node->id());
    $this->assertText('Tag 1');
    $this->assertText('Tag 2');
    $edit = array(
      'fields[field_tags][settings_edit_form][third_party_settings][ds][ds_limit]' => '1',
    );
    $this->dsEditLimitSettings($edit, 'field_tags');
    $this->drupalGet('node/' . $node->id());
    $this->assertText('Tag 1');
    $this->assertNoText('Tag 2');

    // Test \Drupal\Component\Utility\Html::escape() on ds_render_field() with the title field.
    $edit = array(
      'fields[node_title][region]' => 'right',
    );
    $this->dsConfigureUI($edit, 'admin/structure/types/manage/article/display');
    $edit = array(
      'title[0][value]' => 'Hi, I am an article <script>alert(\'with a javascript tag in the title\');</script>',
    );
    $this->drupalPostForm('node/' . $node->id() . '/edit', $edit, t('Save and keep published'));
    $this->drupalGet('node/' . $node->id());
    $this->assertRaw('<h2>Hi, I am an article &lt;script&gt;alert(&#039;with a javascript tag in the title&#039;);&lt;/script&gt;</h2>');
  }

}
