<?php

namespace Drupal\block_visibility_groups\Tests;

/**
 * Tests the block_visibility_groups UI.
 *
 * @group block_visibility_groups
 */
class BlockVisibilityGroupsUITest extends BlockVisibilityGroupsTestBase {

  /**
   * Test for the creation of block visibility groups.
   */
  public function testBlockVisibilityCreation() {
    // Enable action and task blocks.
    $this->drupalPlaceBlock('local_actions_block');
    $this->drupalPlaceBlock('local_tasks_block');

    // Test block visibility tab exists and works.
    $this->drupalGet('admin/structure/block');
    $this->assertLink('Block Visibility Groups');
    $this->clickLink('Block Visibility Groups');
    $this->assertResponse(200);

    // Test add block visibilty button exists and works.
    $this->assertText(t('There is no Block Visibility Group yet.'), 'No visibilty group.');
    $this->assertLink('Add Block Visibility Group');
    $this->clickLink('Add Block Visibility Group');
    $this->assertResponse(200);

    // Fill and submit form for block visibilty groups creation.
    $this->assertFieldById('edit-label');
    $edit = [
      'label' => $this->randomMachineName(),
      'id' => 'test_block_visibility_groups',
    ];
    $this->drupalPostForm(NULL, $edit, t('Save'));

    // Block visibility created successfully or not.
    $this->assertText(t('Saved the @group Block Visibility Group.', ['@group' => $edit['label']]));
    $this->assertText($edit['label']);
  }

}
