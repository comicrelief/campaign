<?php

namespace Drupal\Tests\ds\FunctionalJavascript;

use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\FunctionalJavascriptTests\JavascriptTestBase;

/**
 * Tests javascript behavior for the admin UI.
 *
 * @group ds
 */
class JavascriptTest extends JavascriptTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = array(
    'node',
    'user',
    'field_ui',
    'ds',
    'layout_plugin',
  );

  /**
   * The created user.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $adminUser;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    // Create a test user.
    $this->adminUser = $this->drupalCreateUser(array(
      'access content',
      'admin display suite',
      'admin fields',
      'administer nodes',
      'administer content types',
      'administer node fields',
      'administer node form display',
      'administer node display',
    ));
    $this->drupalLogin($this->adminUser);

    $this->drupalCreateContentType(array(
      'type' => 'article',
      'name' => 'Article',
    ));

  }

  /**
   * Test DS settings.
   */
  public function testSettings() {
    // Go to the article manage display page.
    $this->drupalGet('admin/structure/types/manage/article/display');
    $page = $this->getSession()->getPage();

    // Change the layout to 2 column layout and wait for it to be changed, see
    // if the new template is displayed.
    $page->selectFieldOption('layout', 'ds_2col');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->assertSession()->pageTextContains('ds-2col--node.html.twig');
    $page->pressButton('Save');

    // Check that all settings are saved.
    /** @var \Drupal\Core\Entity\Display\EntityViewDisplayInterface $display */
    $display = EntityViewDisplay::load('node.article.default');
    $settings = $display->getThirdPartySetting('ds', 'layout');
    $this->assertSame($settings['id'], 'ds_2col');
  }

}
