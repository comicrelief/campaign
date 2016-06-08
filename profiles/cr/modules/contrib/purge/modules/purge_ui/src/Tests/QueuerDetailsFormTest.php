<?php

/**
 * @file
 * Contains \Drupal\purge_ui\Tests\QueuerDetailsFormTest.
 */

namespace Drupal\purge_ui\Tests;

use Drupal\Core\Url;
use Drupal\purge\Tests\WebTestBase;

/**
 * Tests:
 *   - \Drupal\purge_ui\Form\PluginDetailsForm.
 *   - \Drupal\purge_ui\Controller\QueuerFormController::detailForm().
 *   - \Drupal\purge_ui\Controller\QueuerFormController::detailFormTitle().
 *
 * @group purge_ui
 */
class QueuerDetailsFormTest extends WebTestBase {

  /**
   * @var \Drupal\user\Entity\User
   */
  protected $admin_user;

  /**
   * The route that renders the form.
   *
   * @var string
   */
  protected $route = 'purge_ui.queuer_detail_form';

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['purge_queuer_test', 'purge_ui'];

  /**
   * Setup the test.
   */
  function setUp() {
    parent::setUp();
    $this->admin_user = $this->drupalCreateUser(['administer site configuration']);
  }

  /**
   * Tests permissions, the form controller and general form returning.
   */
  public function testAccess() {
    $args = ['id' => 'a'];
    $this->initializeQueuersService(['a']);
    $this->drupalGet(Url::fromRoute($this->route, $args));
    $this->assertResponse(403);
    $this->drupalLogin($this->admin_user);
    $this->drupalGet(Url::fromRoute($this->route, $args));
    $this->assertResponse(200);
    $args = ['id' => 'doesnotexist'];
    $this->drupalGet(Url::fromRoute($this->route, $args));
    $this->assertResponse(404);
  }

  /**
   * Tests that the close button works and that content exists.
   *
   * @see \Drupal\purge_ui\Form\QueuerDetailForm::buildForm
   * @see \Drupal\purge_ui\Form\CloseDialogTrait::closeDialog
   */
  public function testDetailForm() {
    $args = ['id' => 'a'];
    $this->initializeQueuersService(['a']);
    $this->drupalLogin($this->admin_user);
    $this->drupalGet(Url::fromRoute($this->route, $args));
    $this->assertRaw('Queuer A');
    $this->assertRaw('Test queuer A.');
    $this->assertRaw(t('Close'));
    $json = $this->drupalPostAjaxForm(Url::fromRoute($this->route, $args)->toString(), [], ['op' => t('Close')]);
    $this->assertEqual('closeDialog', $json[1]['command']);
    $this->assertEqual(2, count($json));
  }

}
