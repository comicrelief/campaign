<?php

namespace Drupal\paragraphs\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Tests that Paragraphs module can be uninstalled.
 *
 * @group paragraphs
 */
class ParagraphsUninstallTest extends WebTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('paragraphs_demo');

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $admin_user = $this->drupalCreateUser(array(
      'administer paragraphs types',
      'administer modules',
    ));
    $this->drupalLogin($admin_user);
  }

  /**
   * Tests that Paragraphs module can be uninstalled.
   */
  public function testUninstall() {

    // Delete Paragraphs data.
    $this->drupalPostForm('admin/structure/paragraphs_type/uninstall', [], t('Delete Paragraphs data'));
    $this->assertText(t('Paragraphs data has been deleted.'));

    // Uninstall the module.
    $this->drupalPostForm('admin/modules/uninstall', ['uninstall[paragraphs_demo]' => TRUE], t('Uninstall'));
    $this->drupalPostForm(NULL, [], t('Uninstall'));
    $this->drupalPostForm('admin/modules/uninstall', ['uninstall[paragraphs]' => TRUE], t('Uninstall'));
    $this->drupalPostForm(NULL, [], t('Uninstall'));
    $this->assertText(t('The selected modules have been uninstalled.'));
    $this->assertNoText(t('Paragraphs demo'));
    $this->assertNoText(t('Paragraphs'));
  }

}
