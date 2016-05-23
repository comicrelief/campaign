<?php
/**
 * @file
 * Contains \Drupal\metatag\Tests\MetatagFrontpageTest.
 */

namespace Drupal\metatag\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Ensures that metatags are rendering correctly on home page.
 *
 * @group Metatag
 */
class MetatagFrontpageTest extends WebTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = array(
    'token',
    'metatag',
    'node',
    'system',
    'test_page_test',
  );

  /**
   * The path to a node that is created for testing.
   *
   * @var string
   */
  protected $nodeId;

  /**
   * Administrator user for tests.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $adminUser;

  /**
   * Setup basic environment.
   */
  protected function setUp() {
    parent::setUp();

    $admin_permissions = [
      'administer content types',
      'administer nodes',
      'bypass node access',
      'administer meta tags',
      'administer site configuration',
      'access content',
    ];

    // Create and login user.
    $this->adminUser = $this->drupalCreateUser($admin_permissions);
    $this->drupalLogin($this->adminUser);

    // Create content type.
    $this->drupalCreateContentType(array('type' => 'page', 'display_submitted' => FALSE));
    $this->nodeId = $this->drupalCreateNode(
      array(
        'title' => $this->randomMachineName(8),
        'promote' => 1,
      ))->id();

    $this->config('system.site')->set('page.front', '/node/' . $this->nodeId)->save();
  }

  /**
   * The front page config is enabled, its meta tags should be used.
   */
  public function testFrontPageMetatagsEnabledConfig() {
    $this->drupalLogin($this->adminUser);

    // Add something to the front page config.
    $values = array(
      'title' => 'Test title',
      'description' => 'Test description',
      'keywords' => 'testing,keywords'
    );
    $this->drupalPostForm('admin/config/search/metatag/front', $values, t('Save'));
    $this->assertResponse(200);
    $this->assertText(t('Saved the Front page Metatag defaults.'));

    // Testing front page metatags.
    $this->drupalGet('<front>');
    foreach ($values as $metatag => $metatag_value) {
      $xpath = $this->xpath("//meta[@name='" . $metatag . "']");
      $this->assertEqual(count($xpath), 1, 'Exactly one ' . $metatag . ' meta tag found.');
      $value = (string) $xpath[0]['content'];
      $this->assertEqual($value, $metatag_value);
    }

    $node_path = '/node/' . $this->nodeId;
    // Testing front page metatags.
    $this->drupalGet($node_path);
    foreach ($values as $metatag => $metatag_value) {
      $xpath = $this->xpath("//meta[@name='" . $metatag . "']");
      $this->assertEqual(count($xpath), 1, 'Exactly one ' . $metatag . ' meta tag found.');
      $value = (string) $xpath[0]['content'];
      $this->assertEqual($value, $metatag_value);
    }

    // Change the front page to a valid custom route.
    $edit['site_frontpage'] = '/test-page';
    $this->drupalPostForm('admin/config/system/site-information', $edit, t('Save configuration'));
    $this->assertText(t('The configuration options have been saved.'), 'The front page path has been saved.');

    $this->drupalGet('test-page');
    foreach ($values as $metatag => $metatag_value) {
      $xpath = $this->xpath("//meta[@name='" . $metatag . "']");
      $this->assertEqual(count($xpath), 1, 'Exactly one ' . $metatag . ' meta tag found.');
      $value = (string) $xpath[0]['content'];
      $this->assertEqual($value, $metatag_value);
    }
  }

  /**
   * Test front page metatags when front page config is disabled.
   */
  public function testFrontPageMetatagDisabledConfig() {
    // Disable front page metatag, enable node metatag & check.
    $this->drupalPostForm('admin/config/search/metatag/front/delete', [], t('Delete'));
    $this->assertResponse(200);
    $this->assertText(t('Deleted Front page defaults.'));

    // Update the Metatag Node defaults.
    $values = array(
      'title' => 'Test title for a node.',
      'description' => 'Test description for a node.',
    );
    $this->drupalPostForm('admin/config/search/metatag/node', $values, 'Save');
    $this->assertText('Saved the Content Metatag defaults.');
    $this->drupalGet('<front>');
    foreach ($values as $metatag => $metatag_value) {
      $xpath = $this->xpath("//meta[@name='" . $metatag . "']");
      $this->assertEqual(count($xpath), 1, 'Exactly one ' . $metatag . ' meta tag found.');
      $value = (string) $xpath[0]['content'];
      $this->assertEqual($value, $metatag_value);
    }

    // Front page is custom route.
    // Update the Metatag Node global.
    $values = array(
      'title' => 'Test title.',
      'description' => 'Test description.',
    );
    $this->drupalPostForm('admin/config/search/metatag/global', $values, 'Save');
    $this->assertText('Saved the Global Metatag defaults.');

    // Change the front page to a valid path.
    $edit['site_frontpage'] = '/test-page';
    $this->drupalPostForm('admin/config/system/site-information', $edit, t('Save configuration'));
    $this->assertText(t('The configuration options have been saved.'), 'The front page path has been saved.');

    // Test Metatags.
    $this->drupalGet('test-page');
    foreach ($values as $metatag => $metatag_value) {
      $xpath = $this->xpath("//meta[@name='" . $metatag . "']");
      $this->assertEqual(count($xpath), 1, 'Exactly one ' . $metatag . ' meta tag found.');
      $value = (string) $xpath[0]['content'];
      $this->assertEqual($value, $metatag_value);
    }
  }

}
