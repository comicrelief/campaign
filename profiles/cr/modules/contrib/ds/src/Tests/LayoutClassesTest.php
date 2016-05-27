<?php

/**
 * @file
 * Contains \Drupal\ds\Tests\LayoutClassesTest.
 */

namespace Drupal\ds\Tests;

use Drupal\Core\Entity\Entity\EntityViewDisplay;

/**
 * Tests for managing layouts and classes on Field UI screen.
 *
 * @group ds
 */
class LayoutClassesTest extends FastTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setup() {
    parent::setup();

    // Set extra fields
    \Drupal::configFactory()->getEditable('ds_extras.settings')
      ->set('region_to_block', TRUE)
      ->set('fields_extra', TRUE)
      ->set('fields_extra_list', array('node|article|ds_extras_extra_test_field', 'node|article|ds_extras_second_field'))
      ->save();

    \Drupal::service('entity_field.manager')->clearCachedFieldDefinitions();
  }

  /**
   * Test selecting layouts, classes, region to block and fields.
   */
  function testDStestLayouts() {
    // Check that the ds_3col_equal_width layout is not available (through the alter).
    $this->drupalGet('admin/structure/types/manage/article/display');
    $this->assertNoRaw('ds_3col_stacked_equal_width', 'ds_3col_stacked_equal_width not available');

    // Create code and block field.
    $this->dsCreateTokenField();
    $this->dsCreateBlockField();

    $layout = array(
      'layout' => 'ds_2col_stacked',
    );

    $assert = array(
      'regions' => array(
        'header' => '<td colspan="8">' . t('Header') . '</td>',
        'left' => '<td colspan="8">' . t('Left') . '</td>',
        'right' => '<td colspan="8">' . t('Right') . '</td>',
        'footer' => '<td colspan="8">' . t('Footer') . '</td>',
      ),
    );

    $fields = array(
      'fields[node_post_date][region]' => 'header',
      'fields[node_author][region]' => 'left',
      'fields[node_links][region]' => 'left',
      'fields[body][region]' => 'right',
      'fields[dynamic_token_field:node-test_field][region]' => 'left',
      'fields[dynamic_block_field:node-test_block_field][region]' => 'left',
      'fields[node_submitted_by][region]' => 'left',
      'fields[ds_extras_extra_test_field][region]' => 'header',
    );

    // Setup first layout.
    $this->dsSelectLayout($layout, $assert);
    $this->dsConfigureClasses();
    $this->dsSelectClasses();
    $this->dsConfigureUI($fields);

    // Assert the two extra fields are found.
    $this->drupalGet('admin/structure/types/manage/article/display');
    $this->assertRaw('ds_extras_extra_test_field');
    $this->assertRaw('ds_extras_second_field');

    // Assert we have configuration.
    $entity_display = entity_load('entity_view_display', 'node.article.default');
    $data = $entity_display->getThirdPartySettings('ds');

    $this->assertTrue(!empty($data), t('Configuration found for layout settings for node article'));
    $this->assertTrue(in_array('ds_extras_extra_test_field', $data['regions']['header']), t('Extra field is in header'));
    $this->assertTrue(in_array('node_post_date', $data['regions']['header']), t('Post date is in header'));
    $this->assertTrue(in_array('dynamic_token_field:node-test_field', $data['regions']['left']), t('Test field is in left'));
    $this->assertTrue(in_array('node_author', $data['regions']['left']), t('Author is in left'));
    $this->assertTrue(in_array('node_links', $data['regions']['left']), t('Links is in left'));
    $this->assertTrue(in_array('dynamic_block_field:node-test_block_field', $data['regions']['left']), t('Test block field is in left'));
    $this->assertTrue(in_array('body', $data['regions']['right']), t('Body is in right'));
    $this->assertTrue(in_array('class_name_1', $data['layout']['settings']['classes']['header']), t('Class name 1 is in header'));
    $this->assertTrue(empty($data['layout']['settings']['classes']['left']), t('Left has no classes'));
    $this->assertTrue(empty($data['layout']['settings']['classes']['right']), t('Right has classes'));
    $this->assertTrue(in_array('class_name_2', $data['layout']['settings']['classes']['footer']), t('Class name 2 is in header'));

    // Create a article node and verify settings.
    $settings = array(
      'type' => 'article',
    );
    $node = $this->drupalCreateNode($settings);
    $this->drupalGet('node/' . $node->id());

    // Assert regions.
    $this->assertRaw('group-header', 'Template found (region header)');
    $this->assertRaw('class_name_1 group-header', 'Class found (class_name_1)');
    $this->assertRaw('group-left', 'Template found (region left)');
    $this->assertRaw('group-right', 'Template found (region right)');
    $this->assertRaw('group-footer', 'Template found (region footer)');
    $this->assertRaw('class_name_2 group-footer', 'Class found (class_name_2)');

    // Assert custom fields.
    $this->assertRaw('field--name-dynamic-token-fieldnode-test-field', t('Custom field found'));
    $this->assertRaw('field--name-dynamic-block-fieldnode-test-block-field', t('Custom block field found'));

    $this->assertRaw('Submitted by', t('Submitted field found'));
    $this->assertText('This is an extra field made available through "Extra fields" functionality.');

    // Test HTML5 wrappers
    $this->assertNoRaw('<header class="class_name_1 group-header', 'Header not found.');
    $this->assertNoRaw('<footer class="group-right', 'Footer not found.');
    $this->assertNoRaw('<article', 'Article not found.');
    $wrappers = array(
      'layout_configuration[region_wrapper][header]' => 'header',
      'layout_configuration[region_wrapper][right]' => 'footer',
      'layout_configuration[region_wrapper][outer_wrapper]' => 'article',
    );
    $this->dsConfigureUI($wrappers);
    $this->drupalGet('node/' . $node->id());
    $this->assertRaw('<header class="class_name_1 group-header', 'Header found.');
    $this->assertRaw('<footer class="group-right', 'Footer found.');
    $this->assertRaw('<article', 'Article found.');

    // Let's create a block field, enable the full mode first.
    $edit = array('display_modes_custom[full]' => '1');
    $this->drupalPostForm('admin/structure/types/manage/article/display', $edit, t('Save'));

    // Select layout.
    $layout = array(
      'layout' => 'ds_2col',
    );

    $assert = array(
      'regions' => array(
        'left' => '<td colspan="8">' . t('Left') . '</td>',
        'right' => '<td colspan="8">' . t('Right') . '</td>',
      ),
    );
    $this->dsSelectLayout($layout, $assert, 'admin/structure/types/manage/article/display/full');

    // Create new block field.
    $edit = array(
      'new_block_region' => 'Block region',
      'new_block_region_key' => 'block_region',
    );
    $this->drupalPostForm('admin/structure/types/manage/article/display/full', $edit, t('Save'));
    $this->assertRaw('<td colspan="9">' . t('Block region') . '</td>', 'Block region found');

    // Configure fields
    $fields = array(
      'fields[node_author][region]' => 'left',
      'fields[node_links][region]' => 'left',
      'fields[body][region]' => 'right',
      'fields[dynamic_token_field:node-test_field][region]' => 'block_region',
    );
    $this->dsConfigureUI($fields, 'admin/structure/types/manage/article/display/full');

    // Set block in sidebar

    // @todo fix this

    /*
    $edit = array(
      'blocks[ds_extras_block_region][region]' => 'sidebar_first',
    );
    $this->drupalPostForm('admin/structure/block', $edit, t('Save blocks'));

    // Assert the block is on the node page.
    $this->drupalGet('node/' . $node->id());
    $this->assertRaw('Block region</h2>', 'Block region found');
    $this->assertText('Test code field on node ' . $node->id(), 'Post date in block');
    */

    // Change layout via admin/structure/ds/layout-change.
    // First verify that header and footer are not here.
    $this->drupalGet('admin/structure/types/manage/article/display/full');
    $this->assertNoRaw('<td colspan="8">' . t('Header') . '</td>', 'Header region not found');
    $this->assertNoRaw('<td colspan="8">' . t('Footer') . '</td>', 'Footer region not found');

    // Remap the regions.
    $edit = array(
      'ds_left' => 'header',
      'ds_right' => 'footer',
      'ds_block_region' => 'footer',
    );
    $this->drupalPostForm('admin/structure/ds/change-layout/node/article/full/ds_2col_stacked', $edit, t('Save'));
    $this->drupalGet('admin/structure/types/manage/article/display/full');

    // Verify new regions.
    $this->assertRaw('<td colspan="9">' . t('Header') . '</td>', 'Header region found');
    $this->assertRaw('<td colspan="9">' . t('Footer') . '</td>', 'Footer region found');
    $this->assertRaw('<td colspan="9">' . t('Block region') . '</td>', 'Block region found');

    // Verify settings.
    $entity_display = EntityViewDisplay::load('node.article.full', TRUE);
    $data = $entity_display->getThirdPartySettings('ds');
    $this->assertTrue(in_array('node_author', $data['regions']['header']), t('Author is in header'));
    $this->assertTrue(in_array('node_links', $data['regions']['header']), t('Links field is in header'));
    $this->assertTrue(in_array('body', $data['regions']['footer']), t('Body field is in footer'));
    $this->assertTrue(in_array('dynamic_token_field:node-test_field', $data['regions']['footer']), t('Test field is in footer'));

    // Test that a default view mode with no layout is not affected by a disabled view mode.
    $edit = array(
      'layout' => '',
      'display_modes_custom[full]' => FALSE,
    );
    $this->drupalPostForm('admin/structure/types/manage/article/display', $edit, t('Save'));
    $this->drupalGet('node/' . $node->id());
    $this->assertNoText('Test code field on node 1', 'No ds field from full view mode layout');
  }
}
