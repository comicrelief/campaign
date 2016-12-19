<?php

namespace Drupal\search_api\Tests;

use Drupal\Core\Url;
use Drupal\system\Tests\Menu\LocalActionTest;

/**
 * Tests that local actions are available.
 *
 * @group search_api
 */
class LocalActionsWebTest extends LocalActionTest {

  /**
   * Modules to enable for this test.
   *
   * @var string[]
   */
  public static $modules = array('search_api');

  /**
   * The administrator account to use for the tests.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $adminUser;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    // Create users.
    $this->adminUser = $this->drupalCreateUser(array('administer search_api', 'access administration pages'));
    $this->drupalLogin($this->adminUser);

    // Do not use a batch for tracking the initial items after creating an
    // index when running the tests via the GUI. Otherwise, it seems Drupal's
    // Batch API gets confused and the test fails.
    if (php_sapi_name() != 'cli') {
      \Drupal::state()->set('search_api_use_tracking_batch', FALSE);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function testLocalAction() {
    // @todo Merge into OverviewPageTest or IntegrationTest? Or get rid of the
    //   triple loop, or do something useful with it.
    foreach ($this->getSearchApiPageRoutes() as $routes) {
      foreach ($routes as $route) {
        $actions = array(
          [Url::fromRoute('entity.search_api_server.add_form'), 'Add server'],
          [Url::fromRoute('entity.search_api_index.add_form'), 'Add index'],
        );
        $this->drupalGet($route);
        $this->assertLocalAction($actions);
      }
    }
  }

  /**
   * Provides a list of routes to test.
   */
  public function getSearchApiPageRoutes() {
    return array(
      array('/admin/config/search/search-api'),
    );
  }

}
