<?php

/**
 * @file
 * Contains \Drupal\field_ui\Tests\ManageFieldsTest.
 */

namespace Drupal\ds\Tests;

use Drupal\Core\Language\LanguageInterface;
use Drupal\field\Tests\EntityReference\EntityReferenceTestTrait;
use Drupal\field_ui\Tests\FieldUiTestTrait;
use Drupal\simpletest\WebTestBase;
use Drupal\taxonomy\Entity\Term;
use Drupal\taxonomy\Entity\Vocabulary;
use Drupal\taxonomy\Tests\TaxonomyTestTrait;
use Drupal\user\Entity\User;

/**
 * Base test for Display Suite.
 *
 * @group ds
 */
abstract class FastTestBase extends WebTestBase {

  use DsTestTrait;
  use EntityReferenceTestTrait;
  use FieldUiTestTrait;
  use TaxonomyTestTrait;

  /**
   * Modules to install.
   *
   * @var array
   */
  public static $modules = array('node', 'user', 'field_ui', 'rdf', 'quickedit', 'taxonomy', 'block', 'ds', 'ds_extras', 'ds_test', 'ds_switch_view_mode', 'layout_plugin', 'field_group');

  /**
   * The label for a random field to be created for testing.
   *
   * @var string
   */
  protected $fieldLabel;

  /**
   * The input name of a random field to be created for testing.
   *
   * @var string
   */
  protected $fieldNameInput;

  /**
   * The name of a random field to be created for testing.
   *
   * @var string
   */
  protected $fieldName;

  /**
   * The created taxonomy vocabulary.
   *
   * @var \Drupal\taxonomy\Entity\Vocabulary
   */
  protected $vocabulary;

  /**
   * The created user
   *
   * @var User
   */
  protected $adminUser;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->drupalPlaceBlock('system_breadcrumb_block');
    $this->drupalPlaceBlock('local_tasks_block');

    // Create a test user.
    $this->adminUser = $this->drupalCreateUser(array(
      'access content',
      'admin classes',
      'admin display suite',
      'admin fields',
      'administer nodes',
      'view all revisions',
      'administer content types',
      'administer node fields',
      'administer node form display',
      'administer node display',
      'administer taxonomy',
      'administer taxonomy_term fields',
      'administer taxonomy_term display',
      'administer users',
      'administer permissions',
      'administer account settings',
      'administer user display',
      'administer software updates',
      'access site in maintenance mode',
      'administer site configuration',
      'bypass node access',
      'ds switch view mode'
    ));
    $this->drupalLogin($this->adminUser);

    // Create random field name.
    $this->fieldLabel = $this->randomMachineName(8);
    $this->fieldNameInput = strtolower($this->randomMachineName(8));
    $this->fieldName = 'field_' . $this->fieldNameInput;

    // Create Article node type.
    $this->drupalCreateContentType(array('type' => 'article', 'name' => 'Article', 'revision' => TRUE));
    $this->drupalCreateContentType(array('type' => 'page', 'name' => 'Page', 'revision' => TRUE));

    // Create a vocabulary named "Tags".
    $this->vocabulary = Vocabulary::create(array(
      'name' => 'Tags',
      'vid' => 'tags',
      'langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED,
    ));
    $this->vocabulary->save();

    $term1 = Term::create(array(
      'name' => 'Tag 1',
      'vid' => 'tags',
      'langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED,
    ));
    $term1->save();

    $term2 = Term::create(array(
      'name' => 'Tag 2',
      'vid' => 'tags',
      'langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED,
    ));
    $term2->save();

    $handler_settings = array(
      'target_bundles' => array(
        $this->vocabulary->id() => $this->vocabulary->id(),
      ),
      // Enable auto-create.
      'auto_create' => TRUE,
    );
    $this->createEntityReferenceField('node', 'article', 'field_' . $this->vocabulary->id(), 'Tags', 'taxonomy_term', 'default', $handler_settings, 10);

    entity_get_form_display('node', 'article', 'default')
      ->setComponent('field_' . $this->vocabulary->id())
      ->save();
  }

  /**
   * Check to see if two trimmed values are equal.
   */
  protected function assertTrimEqual($first, $second, $message = '', $group = 'Other') {
    $first = (string) $first;
    $second = (string) $second;

    return $this->assertEqual(trim($first), trim($second), $message, $group);
  }
}
