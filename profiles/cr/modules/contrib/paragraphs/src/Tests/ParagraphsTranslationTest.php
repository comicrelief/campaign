<?php
/**
 * @file
 * Contains \Drupal\paragraphs\ParagraphsTranslationTest.
 */

namespace Drupal\paragraphs\Tests;

use Drupal\Core\Entity\Entity;
use Drupal\simpletest\WebTestBase;

/**
 * Tests the configuration of paragraphs.
 *
 * @group paragraphs
 */
class ParagraphsTranslationTest extends WebTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array(
    'node',
    'paragraphs_demo',
    'content_translation',
    'block',
  );

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->drupalPlaceBlock('local_tasks_block');
    $this->drupalPlaceBlock('page_title_block');
  }

  /**
   * Tests the paragraph translation.
   */
  public function testParagraphTranslation() {
    $admin_user = $this->drupalCreateUser(array(
      'administer site configuration',
      'administer nodes',
      'create paragraphed_content_demo content',
      'edit any paragraphed_content_demo content',
      'delete any paragraphed_content_demo content',
      'administer paragraph form display',
      'administer content translation',
      'translate any entity',
      'create content translations',
      'administer languages',
    ));

    $this->drupalLogin($admin_user);

    $this->drupalGet('admin/config/regional/content-language');

    // Check the settings are saved correctly.
    $this->assertFieldChecked('edit-entity-types-paragraph');
    $this->assertFieldChecked('edit-settings-node-paragraphed-content-demo-translatable');
    $this->assertFieldChecked('edit-settings-paragraph-text-image-translatable');

    // Check if the publish/unpublish option works.
    $this->drupalGet('admin/structure/paragraphs_type/text_image/form-display');
    $edit = array(
      'fields[status][type]' => 'boolean_checkbox',
    );

    $this->drupalPostForm(NULL, $edit, t('Save'));
    $this->drupalGet('node/add/paragraphed_content_demo');
    $this->drupalPostForm(NULL, NULL, t('Add Text + Image'));
    $this->assertRaw('edit-field-paragraphs-demo-0-subform-status-value');
    $edit = [
      'title[0][value]' => 'Example publish/unpublish',
      'field_paragraphs_demo[0][subform][field_text_demo][0][value]' => 'Example published and unpublished',
    ];
    $this->drupalPostForm(NULL, $edit, t('Save and publish'));
    $this->assertText(t('Example published and unpublished'));
    $this->clickLink(t('Edit'));
    $edit = [
      'field_paragraphs_demo[0][subform][status][value]' => FALSE,
    ];
    $this->drupalPostForm(NULL, $edit, t('Save and keep published'));
    $this->assertNoText(t('Example published and unpublished'));

    // Add paragraphed content.
    $this->drupalGet('node/add/paragraphed_content_demo');
    $this->drupalPostForm(NULL, NULL, t('Add Text + Image'));
    $edit = array(
      'title[0][value]' => 'Title in english',
      'field_paragraphs_demo[0][subform][field_text_demo][0][value]' => 'Text in english',
    );
    // The button to remove a paragraph is present.
    $this->assertRaw(t('Remove'));
    $this->drupalPostForm(NULL, $edit, t('Save and publish'));
    $node = $this->drupalGetNodeByTitle('Title in english');
    // The text is present when editing again.
    $this->clickLink(t('Edit'));
    $this->assertText('Title in english');
    $this->assertText('Text in english');

    // Add french translation.
    $this->clickLink(t('Translate'));
    $this->clickLink(t('Add'), 1);
    // Make sure the Add / Remove paragraph buttons are hidden.
    $this->assertNoRaw(t('Remove'));
    $this->assertNoRaw(t('Add Text + Image'));
    // Make sure that the original paragraph text is displayed.
    $this->assertText('Text in english');

    $edit = array(
      'title[0][value]' => 'Title in french',
      'field_paragraphs_demo[0][subform][field_text_demo][0][value]' => 'Text in french',
    );
    $this->drupalPostForm(NULL, $edit, t('Save and keep published (this translation)'));
    $this->assertText('Paragraphed article Title in french has been updated.');

    // Check the english translation.
    $this->drupalGet('node/' . $node->id());
    $this->assertText('Title in english');
    $this->assertText('Text in english');
    $this->assertNoText('Title in french');
    $this->assertNoText('Text in french');

    // Check the french translation.
    $this->drupalGet('fr/node/' . $node->id());
    $this->assertText('Title in french');
    $this->assertText('Text in french');
    $this->assertNoText('Title in english');
    // The translation is still present when editing again.
    $this->clickLink(t('Edit'));
    $this->assertText('Title in french');
    $this->assertText('Text in french');
    $edit = array(
      'field_paragraphs_demo[0][subform][field_text_demo][0][value]' => 'New text in french',
    );
    $this->drupalPostForm(NULL, $edit, t('Save and keep published (this translation)'));
    $this->assertText('Title in french');
    $this->assertText('New text in french');

    // Back to the source language.
    $this->drupalGet('node/' . $node->id());
    $this->clickLink(t('Edit'));
    $this->assertText('Title in english');
    $this->assertText('Text in english');
    // Save the original content on second request.
    $this->drupalPostForm(NULL, NULL, t('Save and keep published (this translation)'));
    $this->assertText('Paragraphed article Title in english has been updated.');
  }

}
