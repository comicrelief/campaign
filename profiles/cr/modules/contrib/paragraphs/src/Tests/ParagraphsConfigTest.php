<?php

namespace Drupal\paragraphs\Tests;

use Drupal\language\Entity\ConfigurableLanguage;

/**
 * Tests paragraphs configuration.
 *
 * @group paragraphs
 */
class ParagraphsConfigTest extends ParagraphsTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array(
    'content_translation',
  );

  /**
   * Tests adding paragraphs with no translation enabled.
   */
  public function testFieldTranslationDisabled() {
    $this->loginAsAdmin([
      'administer languages',
      'administer content translation',
      'create content translations',
      'translate any entity',
    ]);

    // Add a paragraphed content type.
    $this->addParagraphedContentType('paragraphed_test', 'paragraphs_field');
    $this->addParagraphsType('paragraph_type_test');

    // Add a second language.
    ConfigurableLanguage::create(['id' => 'de'])->save();

    // Enable translation for paragraphed content type. Do not enable
    // translation for the ERR paragraphs field nor for fields on the
    // paragraph type.
    $edit = [
      'entity_types[node]' => TRUE,
      'settings[node][paragraphed_test][translatable]' => TRUE,
      'settings[node][paragraphed_test][fields][paragraphs_field]' => FALSE,
    ];
    $this->drupalPostForm('admin/config/regional/content-language', $edit, t('Save configuration'));

    // Create a node with a paragraph.
    $this->drupalPostAjaxForm('node/add/paragraphed_test', [], 'paragraphs_field_paragraph_type_test_add_more');
    $this->drupalPostForm(NULL, ['title[0][value]' => 'paragraphed_title'], t('Save and publish'));

    // Attempt to add a translation.
    $node = $this->drupalGetNodeByTitle('paragraphed_title');
    $this->drupalGet('node/' . $node->id() . '/translations');
    $this->clickLink(t('Add'));
    // Save the translation.
    $this->drupalPostForm(NULL, [], t('Save and keep published (this translation)'));
    $this->assertText('paragraphed_test paragraphed_title has been updated.');
  }
}
