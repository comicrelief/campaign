<?php

namespace Drupal\block_visibility_groups_admin\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Provides automated tests for the block_visibility_groups_admin module.
 */
class GroupListerTest extends WebTestBase {

  /**
   * Drupal\block_visibility_groups\GroupEvaluator definition.
   *
   * @var \Drupal\block_visibility_groups\GroupEvaluator
   */
  protected $block_visibility_groups_group_evaluator;

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name' => "block_visibility_groups_admin GroupLister's controller functionality",
      'description' => 'Test Unit for module block_visibility_groups_admin and controller GroupLister.',
      'group' => 'Other',
    );
  }

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
  }

  /**
   * Tests block_visibility_groups_admin functionality.
   */
  public function testGroupLister() {
    // Check that the basic functions of module block_visibility_groups_admin.
    $this->assertEqual(TRUE, TRUE, 'Test Unit Generated via App Console.');
  }

}
