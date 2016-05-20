<?php

/**
 * @file
 * Contains \Drupal\panels\Tests\PageManagerPanelsStorageIntegrationTest.
 */

namespace Drupal\panels\Tests;

use Drupal\page_manager\Entity\PageVariant;
use Drupal\simpletest\WebTestBase;

/**
 * Tests integration between Page Manager and Panels Storage.
 *
 * @group panels
 */
class PageManagerPanelsStorageIntegrationTest extends WebTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = ['block', 'page_manager', 'page_manager_ui', 'panels_test', 'panels_ipe'];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->drupalPlaceBlock('local_tasks_block');
    $this->drupalPlaceBlock('local_actions_block');
    $this->drupalPlaceBlock('system_branding_block');
    $this->drupalPlaceBlock('page_title_block');

    \Drupal::service('theme_handler')->install(['bartik', 'classy']);
    $this->config('system.theme')->set('admin', 'classy')->save();

    $this->drupalLogin($this->drupalCreateUser(['administer pages', 'access administration pages', 'view the administration theme']));
  }

  /**
   * Tests creating a Panels variant with the IPE.
   */
  public function testPanelsIPE() {
    // Create new page.
    $this->drupalGet('admin/structure/page_manager/add');
    $edit = [
      'id' => 'foo',
      'label' => 'foo',
      'path' => 'testing',
    ];
    $this->drupalPostForm(NULL, $edit, 'Save');

    // Add a Panels variant which uses the IPE.
    $this->clickLink('Add new variant');
    $this->clickLink('Panels');
    $edit = [
      'id' => 'panels_1',
      'label' => 'Default',
      // This option won't be present at all if our integration isn't working!
      'variant_settings[builder]' => 'ipe',
    ];
    $this->drupalPostForm(NULL, $edit, 'Save');

    /** @var \Drupal\page_manager\PageVariantInterface $page_variant */
    $page_variant = PageVariant::load('panels_1');
    /** @var \Drupal\panels\Plugin\DisplayVariant\PanelsDisplayVariant $panels_display */
    $panels_display = $page_variant->getVariantPlugin();

    // Make sure the storage type and id were set to the right value.
    $this->assertEqual($panels_display->getStorageType(), 'page_manager');
    $this->assertEqual($panels_display->getStorageId(), 'panels_1');
  }

}
