<?php

/**
 * @ingroup diff
 */

namespace Drupal\diff\Tests;

use Drupal\language\Entity\ConfigurableLanguage;
use Drupal\system\Tests\Menu\AssertBreadcrumbTrait;

/**
 * Tests the diff revisions overview.
 *
 * @group diff
 */
class DiffRevisionTest extends DiffTestBase {

  use AssertBreadcrumbTrait;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'diff_test',
    'content_translation',
    'field_ui'
  ];

  /**
   * Tests the revision diff overview.
   */
  public function testRevisionDiffOverview() {
    $this->drupalPlaceBlock('system_breadcrumb_block');
    // Login as admin with the required permission.
    $this->loginAsAdmin(['delete any article content']);

    // Create an article.
    $title = 'test_title';
    $edit = array(
      'title[0][value]' => $title,
      'body[0][value]' => '<p>Revision 1</p>
      <p>first_unique_text</p>
      <p>second_unique_text</p>',
    );
    $this->drupalPostForm('node/add/article', $edit, t('Save and publish'));
    $node = $this->drupalGetNodeByTitle($title);
    $created = $node->getCreatedTime();
    $this->drupalGet('node/' . $node->id());

    // Make sure the revision tab doesn't exist.
    $this->assertNoLink('Revisions');

    // Create a second revision, with a comment.
    $edit = array(
      'body[0][value]' => '<p>Revision 2</p>
      <p>first_unique_text</p>
      <p>second_unique_text</p>',
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
    // Assert the revision summary.
    $this->assertUniqueText('Revision 2 comment');
    $this->assertText('Initial revision.');

    // Compare the revisions in standard mode.
    $this->drupalPostForm(NULL, NULL, t('Compare'));
    $this->clickLink('Split fields');
    // Assert breadcrumbs are properly displayed.
    $this->assertRaw('<nav class="breadcrumb"');
    $nid1 = $node->id();
    $trail = [
      '' => 'Home',
      "node" => 'Node',
      "node/$nid1" => $node->label(),
      "node/$nid1/revisions" => 'Revisions',
    ];
    $this->assertBreadcrumb(NULL, $trail);
    // Extract the changes.
    $this->assertText('Body');
    $rows = $this->xpath('//tbody/tr');
    $head = $this->xpath('//thead/tr');
    $diff_row = $rows[1]->td;
    $comment = $head[0]->th[1];
    // Assert the revision comment.
    $this->assertEqual((string) $comment, 'Revision 2 comment');
    // Assert changes made to the body, text 1 changed to 2.
    $this->assertEqual((string) ($diff_row[0]), '1');
    $this->assertEqual((string) ($diff_row[1]), '-');
    $this->assertEqual((string) (($diff_row[2]->span)), '1');
    $this->assertEqual(htmlspecialchars_decode(strip_tags($diff_row[2]->asXML())), '<p>Revision 1</p>');
    $this->assertEqual((string) ($diff_row[3]), '1');
    $this->assertEqual((string) ($diff_row[4]), '+');
    $this->assertEqual((string) (($diff_row[5]->span)), '2');
    $this->assertEqual(htmlspecialchars_decode((strip_tags($diff_row[5]->asXML()))), '<p>Revision 2</p>');

    // Compare the revisions in markdown mode.
    $this->clickLink('Strip tags');
    $rows = $this->xpath('//tbody/tr');
    // Assert breadcrumbs are properly displayed.
    $this->assertRaw('<nav class="breadcrumb"');
    $nid1 = $node->id();
    $trail = [
      '' => 'Home',
      "node" => 'Node',
      "node/$nid1" => $node->label(),
      "node/$nid1/revisions" => 'Revisions',
    ];
    $this->assertBreadcrumb(NULL, $trail);
    // Extract the changes.
    $diff_row = $rows[1]->td;
    // Assert changes made to the body, text 1 changed to 2.
    $this->assertEqual((string) ($diff_row[0]), '-');
    $this->assertEqual((string) (($diff_row[1]->span)), '1');
    $this->assertEqual(htmlspecialchars_decode(strip_tags($diff_row[1]->asXML())), 'Revision 1');
    $this->assertEqual((string) (($diff_row[2])), '+');
    $this->assertEqual((string) (($diff_row[3]->span)), '2');
    $this->assertEqual(htmlspecialchars_decode((strip_tags($diff_row[3]->asXML()))), 'Revision 2');

    // Compare the revisions in single column mode.
    $this->clickLink('Unified fields');
    // Assert breadcrumbs are properly displayed.
    $this->assertRaw('<nav class="breadcrumb"');
    $nid1 = $node->id();
    $trail = [
      '' => 'Home',
      "node" => 'Node',
      "node/$nid1" => $node->label(),
      "node/$nid1/revisions" => 'Revisions',
    ];
    $this->assertBreadcrumb(NULL, $trail);
    // Extract the changes.
    $rows = $this->xpath('//tbody/tr');
    $diff_row = $rows[1]->td;
    // Assert changes made to the body, text 1 changed to 2.
    $this->assertEqual((string) ($diff_row[0]), '1');
    $this->assertEqual((string) ($diff_row[1]), '');
    $this->assertEqual((string) ($diff_row[2]), '-');
    $this->assertEqual((string) (($diff_row[3]->span)), '1');
    $this->assertEqual(htmlspecialchars_decode(strip_tags($diff_row[3]->asXML())), '<p>Revision 1</p>');
    $diff_row = $rows[2]->td;
    $this->assertEqual((string) ($diff_row[0]), '');
    $this->assertEqual((string) ($diff_row[1]), '1');
    $this->assertEqual((string) (($diff_row[2])), '+');
    $this->assertEqual((string) (($diff_row[3]->span)), '2');
    $this->assertEqual(htmlspecialchars_decode((strip_tags($diff_row[3]->asXML()))), '<p>Revision 2</p>');
    $this->assertUniqueText('first_unique_text');
    $this->assertUniqueText('second_unique_text');
    $diff_row = $rows[3]->td;
    $this->assertEqual((string) ($diff_row[0]), '2');
    $this->assertEqual((string) ($diff_row[1]), '2');
    $diff_row = $rows[4]->td;
    $this->assertEqual((string) ($diff_row[0]), '3');
    $this->assertEqual((string) ($diff_row[1]), '3');

    $this->clickLink('Strip tags');
    // Extract the changes.
    $rows = $this->xpath('//tbody/tr');
    $diff_row = $rows[1]->td;

    // Assert changes made to the body, with strip_tags filter and make sure
    // there are no line numbers.
    $this->assertEqual((string) ($diff_row[0]), '-');
    $this->assertEqual((string) (($diff_row[1]->span)), '1');
    $this->assertEqual(htmlspecialchars_decode(strip_tags($diff_row[1]->asXML())), 'Revision 1');
    $diff_row = $rows[2]->td;
    $this->assertEqual((string) (($diff_row[0])), '+');
    $this->assertEqual((string) (($diff_row[1]->span)), '2');
    $this->assertEqual(htmlspecialchars_decode((strip_tags($diff_row[1]->asXML()))), 'Revision 2');

    $this->drupalGet('node/' . $node->id());
    $this->clickLink(t('Revisions'));
    // Revert the revision, confirm.
    $this->clickLink(t('Revert'));
    $this->drupalPostForm(NULL, NULL, t('Revert'));
    $this->assertText('Article ' . $title . ' has been reverted to the revision from');

    // Make sure three revisions are available.
    $rows = $this->xpath('//tbody/tr');
    $this->assertEqual(count($rows), 3);
    // Make sure the reverted comment is there.
    $this->assertText('Copy of the revision from');

    // Delete the first revision (last entry in table).
    $this->clickLink(t('Delete'), 0);
    $this->drupalPostForm(NULL, NULL, t('Delete'));
    $this->assertText('of Article ' . $title . ' has been deleted.');

    // Make sure two revisions are available.
    $rows = $this->xpath('//tbody/tr');
    $this->assertEqual(count($rows), 2);

    // Delete one revision so that we are left with only 1 revision.
    $this->clickLink(t('Delete'), 0);
    $this->drupalPostForm(NULL, NULL, t('Delete'));
    $this->assertText('of Article ' . $title . ' has been deleted.');

    // Make sure we only have 1 revision now.
    $rows = $this->xpath('//tbody/tr');
    $this->assertEqual(count($rows), 1);

    // Assert that there are no radio buttons for revision selection.
    $this->assertNoFieldByXPath('//input[@type="radio"]');
    // Assert that there is no submit button.
    $this->assertNoFieldByXPath('//input[@type="submit"]');

    // Create two new revisions of node.
    $edit = [
      'title[0][value]' => 'new test title',
      'body[0][value]' => '<p>new body</p>',
    ];
    $this->drupalPostForm('node/' . $node->id() . '/edit', $edit, 'Save and keep published');

    $edit = [
      'title[0][value]' => 'newer test title',
      'body[0][value]' => '<p>newer body</p>',
    ];
    $this->drupalPostForm('node/' . $node->id() . '/edit', $edit, 'Save and keep published');

    $this->clickLink(t('Revisions'));
    // Assert the revision summary.
    $this->assertNoUniqueText('Changes on: Title, Body');
    $this->assertText('Copy of the revision from');
    $edit = [
      'radios_left' => 3,
      'radios_right' => 4,
    ];
    $this->drupalPostForm(NULL, $edit, t('Compare'));
    $this->clickLink('Strip tags');
    // Check markdown layout is used when navigating between revisions.
    $rows = $this->xpath('//tbody/tr');
    $diff_row = $rows[3]->td;
    $this->assertEqual(htmlspecialchars_decode(strip_tags($diff_row[3]->asXML())), 'new body');
    $this->clickLink('Next change >');
    // The filter should be the same as the previous screen.
    $rows = $this->xpath('//tbody/tr');
    $diff_row = $rows[3]->td;
    $this->assertEqual(htmlspecialchars_decode(strip_tags($diff_row[3]->asXML())), 'newer body');

    // Get the node, create a new revision that is not the current one.
    $node = $this->getNodeByTitle('newer test title');
    $node->setNewRevision(TRUE);
    $node->isDefaultRevision(FALSE);
    $node->save();
    $this->drupalGet('node/' . $node->id() . '/revisions');

    // Check that the last revision is not the current one.
    $this->assertLink(t('Set as current revision'));
    $text = $this->xpath('//tbody/tr[2]/td[4]/em');
    $this->assertEqual($text[0], 'Current revision');

    // Set the last revision as current.
    $this->clickLink('Set as current revision');
    $this->drupalPostForm(NULL, [], t('Revert'));

    // Check the last revision is set as current.
    $text = $this->xpath('//tbody/tr[1]/td[4]/em');
    $this->assertEqual($text[0], 'Current revision');
    $this->assertNoLink(t('Set as current revision'));
  }

  public function testOverviewPager() {
    $config = \Drupal::configFactory()->getEditable('diff.settings');
    $config->set('general_settings.revision_pager_limit', 10)->save();
    $admin_user = $this->drupalCreateUser(['view article revisions']);
    $this->drupalLogin($admin_user);
    $node = $this->drupalCreateNode([
      'type' => 'article',
    ]);
    // Create 50 more revisions in order to trigger paging on the revisions
    // overview screen.
    for ($i = 0; $i < 15; $i++) {
      $node->setNewRevision(TRUE);
      $node->save();
    }

    // Check the number of elements on the first page.
    $this->drupalGet('node/' . $node->id() . '/revisions');
    $element = $this->xpath('//*[@id="edit-node-revisions-table"]/tbody/tr');
    $this->assertEqual(count($element), 10);
    // Check that the pager exists.
    $this->assertRaw('page=1');

    $this->clickLinkPartialName('Next page');
    // Check the number of elements on the second page.
    $element = $this->xpath('//*[@id="edit-node-revisions-table"]/tbody/tr');
    $this->assertEqual(count($element), 6);
    $this->assertRaw('page=0');
    $this->clickLinkPartialName('Previous page');
  }

  /**
   * Tests the revisions overview error messages.
   */
  public function testRevisionOverviewErrorMessages() {
    // Enable some languages for this test.
    $language = ConfigurableLanguage::createFromLangcode('de');
    $language->save();

    // Login as admin with the required permissions.
    $this->loginAsAdmin([
      'administer node form display',
      'administer languages',
      'administer content translation',
      'create content translations',
      'translate any entity',
    ]);

    // Make article content translatable.
    $edit = [
      'entity_types[node]' => TRUE,
      'settings[node][article][translatable]' => TRUE,
      'settings[node][article][settings][language][language_alterable]' => TRUE,
    ];
    $this->drupalPostForm('admin/config/regional/content-language', $edit, t('Save configuration'));

    // Create an article.
    $title = 'test_title';
    $edit = [
      'title[0][value]' => $title,
      'body[0][value]' => '<p>Revision 1</p>',
    ];
    $this->drupalPostForm('node/add/article', $edit, t('Save and publish'));
    $node = $this->drupalGetNodeByTitle($title);

    // Create a revision, changing the node language to German.
    $edit = [
      'langcode[0][value]' => 'de',
      'body[0][value]' => '<p>Revision 2</p>',
      'revision' => TRUE,
    ];
    $this->drupalPostForm('node/' . $node->id() . '/edit', $edit, t('Save and keep published'));

    // Check the revisions overview, ensure only one revisions is available.
    $this->clickLink(t('Revisions'));
    $rows = $this->xpath('//tbody/tr');
    $this->assertEqual(count($rows), 1);

    // Compare the revisions and assert the first error message.
    $this->drupalPostForm(NULL, NULL, t('Compare'));
    $this->assertText('Multiple revisions are needed for comparison.');

    // Create another revision, changing the node language back to English.
    $edit = [
      'langcode[0][value]' => 'en',
      'body[0][value]' => '<p>Revision 3</p>',
      'revision' => TRUE,
    ];
    $this->drupalPostForm('node/' . $node->id() . '/edit', $edit, t('Save and keep published'));

    // Check the revisions overview, ensure two revisions are available.
    $this->clickLink(t('Revisions'));
    $rows = $this->xpath('//tbody/tr');
    $this->assertEqual(count($rows), 2);
    $this->assertNoFieldChecked('edit-node-revisions-table-0-select-column-one');
    $this->assertFieldChecked('edit-node-revisions-table-0-select-column-two');
    $this->assertNoFieldChecked('edit-node-revisions-table-1-select-column-one');
    $this->assertNoFieldChecked('edit-node-revisions-table-1-select-column-two');

    // Compare the revisions and assert the second error message.
    $this->drupalPostForm(NULL, NULL, t('Compare'));
    $this->assertText('Select two revisions to compare.');

    // Check the same revisions twice and compare.
    $edit = [
      'radios_left' => 3,
      'radios_right' => 3,
    ];
    $this->drupalPostForm('/node/' . $node->id() . '/revisions', $edit, 'Compare');
    // Assert the third error message.
    $this->assertText('Select different revisions to compare.');

    // Check different revisions and compare. This time should work correctly.
    $edit = [
      'radios_left' => 3,
      'radios_right' => 1,
    ];
    $this->drupalPostForm('/node/' . $node->id() . '/revisions', $edit, 'Compare');
    $this->assertLinkByHref('node/1/revisions/view/1/3');
  }

}
