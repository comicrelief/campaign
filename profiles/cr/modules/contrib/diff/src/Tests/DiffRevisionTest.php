<?php
/**
 * @file
 * Contains \Drupal\diff\DiffRevisionTest.
 *
 * @file
 * Diff overview test functions.
 *
 * @ingroup diff
 */

namespace Drupal\diff\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Tests the diff revisions overview.
 *
 * @group diff
 */
class DiffRevisionTest extends WebTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('node', 'diff', 'diff_test', 'block');

  /**
   * Tests the revision diff overview.
   */
  public function testRevisionDiffOverview() {
    $this->drupalPlaceBlock('local_tasks_block');
    $this->drupalPlaceBlock('local_actions_block');
    $this->drupalCreateContentType(array('type' => 'article', 'name' => 'Article'));

    $admin_user = $this->drupalCreateUser(array(
      'administer nodes',
      'administer site configuration',
      'create article content',
      'edit any article content',
      'view article revisions',
      'delete any article content',
    ));
    $this->drupalLogin($admin_user);

    // Create an article.
    $title = $this->randomMachineName();
    $edit = array(
      'title[0][value]' => $title,
      'body[0][value]' => '<p>Revision 1</p>',
      'revision' => TRUE,
    );
    $this->drupalPostForm('node/add/article', $edit, t('Save and publish'));
    $node = $this->drupalGetNodeByTitle($title);
    $created = $node->getCreatedTime();
    $this->drupalGet('node/' . $node->id());

    // Make sure the revision tab doesn't exist.
    $this->assertNoLink('Revisions');

    // Create a second revision, with a comment.
    $edit = array(
      'body[0][value]' => '<p>Revision 2</p>',
      'revision' => TRUE,
      'revision_log[0][value]' => 'Revision 2 comment'
    );
    $this->drupalGet('node/add/article');
    $this->drupalPostForm('node/' . $node->id() . '/edit', $edit, t('Save and keep published'));
    $this->drupalGet('node/' . $node->id());

    // Check the revisions overview.
    $this->clickLink(t('Revisions'));
    $rows = $this->xpath('//tbody/tr');
    // Make sure only two revisions available.
    $this->assertEqual(count($rows), 2);

    // Compare the revisions in standard mode.
    $this->drupalPostForm(NULL, NULL, t('Compare'));
    $this->clickLink('Standard');
    // Extract the changes.
    $this->assertText('Changes to Body');
    $rows = $this->xpath('//tbody/tr');
    $head = $this->xpath('//thead/tr');
    $diff_row = $rows[3]->td;
    $comment = $head[0]->th[3];
    // Assert the revision comment.
    $this->assertEqual((string) $comment, 'Revision 2 comment');
    // Assert changes made to the body, text 1 changed to 2.
    $this->assertEqual((string) ($diff_row[0]), '-');
    $this->assertEqual((string) (($diff_row[1]->span)), '1');
    $this->assertEqual(htmlspecialchars_decode(strip_tags($diff_row[1]->asXML())), '<p>Revision 1</p>');
    $this->assertEqual((string) (($diff_row[2])), '+');
    $this->assertEqual((string) (($diff_row[3]->span)), '2');
    $this->assertEqual(htmlspecialchars_decode((strip_tags($diff_row[3]->asXML()))), '<p>Revision 2</p>');

    // Compare the revisions in markdown mode.
    $this->clickLink('Markdown');
    $rows = $this->xpath('//tbody/tr');
    $diff_row = $rows[3]->td;
    // Assert changes made to the body, text 1 changed to 2.
    $this->assertEqual((string) ($diff_row[0]), '-');
    $this->assertEqual((string) (($diff_row[1]->span)), '1');
    $this->assertEqual(htmlspecialchars_decode(strip_tags($diff_row[1]->asXML())), 'Revision 1');
    $this->assertEqual((string) (($diff_row[2])), '+');
    $this->assertEqual((string) (($diff_row[3]->span)), '2');
    $this->assertEqual(htmlspecialchars_decode((strip_tags($diff_row[3]->asXML()))), 'Revision 2');

    // Go back to revision overview.
    $this->clickLink(t('Back to Revision Overview'));
    // Revert the revision, confirm.
    $this->clickLink(t('Revert'));
    $this->drupalPostForm(NULL, NULL, t('Revert'));
    $this->assertText(t('Article @title has been reverted to the revision from @revision-date.', array(
      '@revision-date' => format_date($created),
      '@title' => $title
    )));

    // Make sure three revisions are available.
    $rows = $this->xpath('//tbody/tr');
    $this->assertEqual(count($rows), 3);
    // Make sure the reverted comment is there.
    $this->assertText(t('Copy of the revision from @date', array('@date' => date('D, m/d/Y - H:i', $created))));

    // Delete the first revision (last entry in table).
    $this->clickLink(t('Delete'), 0);
    $this->drupalPostForm(NULL, NULL, t('Delete'));
    $this->assertText(t('Revision from @date of Article @title has been deleted.', array(
      '@date' => date('D, m/d/Y - H:i', $created),
      '@title' => $title
    )));

    // Make sure two revisions are available.
    $rows = $this->xpath('//tbody/tr');
    $this->assertEqual(count($rows), 2);

    // Delete one revision so that we are left with only 1 revision.
    $this->clickLink(t('Delete'), 0);
    $this->drupalPostForm(NULL, NULL, t('Delete'));
    $this->assertText(t('Revision from @date of Article @title has been deleted.', array(
        '@date' => date('D, m/d/Y - H:i', $created),
        '@title' => $title
    )));

    // Make sure we only have 1 revision now.
    $rows = $this->xpath('//tbody/tr');
    $this->assertEqual(count($rows), 1);

    // Assert that there are no radio buttons for revision selection.
    $this->assertNoFieldByXPath('//input[@type="radio"]');
    // Assert that there is no submit button.
    $this->assertNoFieldByXPath('//input[@type="submit"]');
  }

}
