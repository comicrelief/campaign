<?php
/**
 * @file
 * Contains \Drupal\diff\ViewModeTest.
 *
 * @ingroup diff
 */

namespace Drupal\diff\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Tests field visibility when using a custom view mode.
 *
 * @group diff
 */
class ViewModeTest extends WebTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('node', 'diff', 'field_ui', 'block');

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    // Create the Article content type.
    $this->drupalCreateContentType(array('type' => 'article', 'name' => 'Article'));

    // Place the blocks that Diff module uses.
    $this->drupalPlaceBlock('local_tasks_block');
    $this->drupalPlaceBlock('local_actions_block');

    $this->drupalLogin($this->rootUser);
  }

  /**
   * Tests field visibility using a cutom view mode.
   */
  public function testViewMode() {
    // Set the Article content type to use the diff view mode.
    $edit = [
      'view_mode' => 'diff',
    ];
    $this->drupalPostForm('admin/structure/types/manage/article', $edit, t('Save content type'));
    $this->assertText('The content type Article has been updated.');

    // Specialize the 'diff' view mode, check that the field is displayed the same.
    $edit = array(
      "display_modes_custom[diff]" => TRUE,
    );
    $this->drupalPostForm('admin/structure/types/manage/article/display', $edit, t('Save'));

    // Set the Body field to hidden in the diff view mode.
    $edit = array(
      'fields[body][type]' => 'hidden',
    );
    $this->drupalPostForm('admin/structure/types/manage/article/display/diff', $edit, t('Save'));

    // Create a node.
    $node = $this->drupalCreateNode([
      'type' => 'article',
      'title' => 'Sample node',
      'body' => [
        'value' => 'Foo',
      ],
    ]);

    // Edit the article and change the email.
    $edit = array(
      'body[0][value]' => 'Fighters',
      'revision' => TRUE,
    );
    $this->drupalPostForm('node/' . $node->id() . '/edit', $edit, t('Save and keep published'));

    // Check the difference between the last two revisions.
    $this->clickLink(t('Revisions'));
    $this->drupalPostForm(NULL, NULL, t('Compare'));
    $this->assertNoText('Changes to Body');
    $this->assertNoText('Foo');
    $this->assertNoText('Fighters');
  }

}
