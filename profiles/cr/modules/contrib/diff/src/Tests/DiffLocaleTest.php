<?php

/**
 * @file
 * Contains \Drupal\diff\Tests\DiffLocaleTest.
 */

namespace Drupal\diff\Tests;

use Drupal\Core\Language\Language;
use Drupal\Core\Language\LanguageInterface;
use Drupal\language\Entity\ConfigurableLanguage;
use Drupal\simpletest\WebTestBase;

/**
 * Test diff functionality with localization and translation.
 *
 * @group diff
 */
class DiffLocaleTest extends WebTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('node', 'diff', 'locale', 'content_translation');

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    // Create the Article content type.
    $this->drupalCreateContentType(array('type' => 'article', 'name' => 'Article'));

    $this->drupalLogin($this->rootUser);
  }

  /**
   * Test Diff functionality for the revisions of a translated node.
   */
  function testTranslationRevisions() {
    // Add French language.
    $edit = array(
      'predefined_langcode' => 'fr',
    );
    $this->drupalPostForm('admin/config/regional/language/add', $edit, t('Add language'));

    // Enable content translation on articles.
    $this->drupalGet('admin/config/regional/content-language');
    $edit = array(
      'entity_types[node]' => TRUE,
      'settings[node][article][translatable]' => TRUE,
      'settings[node][article][settings][language][language_alterable]' => TRUE,
    );
    $this->drupalPostForm(NULL, $edit, t('Save configuration'));

    // Create an article and its translation. Assert aliases.
    $edit = array(
      'title[0][value]' => 'English node',
      'langcode[0][value]' => 'en',
    );
    $this->drupalPostForm('node/add/article', $edit, t('Save and publish'));
    $english_node = $this->drupalGetNodeByTitle('English node');

    $this->drupalGet('node/' . $english_node->id() . '/translations');
    $this->clickLink(t('Add'));
    $edit = array(
      'title[0][value]' => 'French node',
    );
    $this->drupalPostForm(NULL, $edit, t('Save and keep published (this translation)'));
    $this->rebuildContainer();
    $english_node = $this->drupalGetNodeByTitle('English node');
    $french_node = $english_node->getTranslation('fr');

    // Create a new revision on both languages.
    $edit = array(
      'title[0][value]' => 'Updated title',
      'revision' => TRUE,
    );
    $this->drupalPostForm('node/' . $english_node->id() . '/edit', $edit, t('Save and keep published (this translation)'));
    $edit = array(
      'title[0][value]' => 'Le titre',
      'revision' => TRUE,
    );
    $this->drupalPostForm('fr/node/' . $english_node->id() . '/edit', $edit, t('Save and keep published (this translation)'));

    // View differences between revisions. Check that they don't mix up.
    $this->drupalGet('node/' . $english_node->id() . '/revisions');
    $this->drupalGet('node/' . $english_node->id() . '/revisions/view/1/2');
    $this->assertText('Changes to Title');
    $this->assertText('English node');
    $this->assertText('Updated title');
    $this->drupalGet('fr/node/' . $english_node->id() . '/revisions');
    $this->drupalGet('fr/node/' . $english_node->id() . '/revisions/view/1/3');
    $this->assertText('Changes to Title');
    $this->assertNoText('English node');
    $this->assertNoText('Updated title');
    $this->assertText('French node');
    $this->assertText('Le titre');
  }

}
