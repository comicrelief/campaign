<?php

/**
 * @file
 * Definition of Drupal\ds\Tests\ManageDisplayTabTest.
 */

namespace Drupal\ds\Tests;
use Drupal\comment\Tests\CommentTestBase;

/**
 * Tests for the manage display tab in Display Suite.
 *
 * @group ds
 */
class CommentTest extends CommentTestBase {

  use DsTestTrait;

  /**
   * Modules to install.
   *
   * @var array
   */
  public static $modules = array('node', 'user', 'comment', 'field_ui', 'block', 'ds', 'layout_plugin');

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
      'administer users',
      'administer permissions',
      'administer account settings',
      'administer user display',
      'administer software updates',
      'access site in maintenance mode',
      'administer site configuration',
      'bypass node access',
      'administer comments',
      'administer comment types',
      'administer comment fields',
      'administer comment display',
      'skip comment approval',
      'post comments',
      'access comments',
      // Usernames aren't shown in comment edit form autocomplete unless this
      // permission is granted.
      'access user profiles',
    ));
    $this->drupalLogin($this->adminUser);
  }

  /**
   * Test adding comments to a node
   */
  public function testComments() {
    // Create a node.
    $settings = array('type' => 'article', 'promote' => 1);
    $node = $this->drupalCreateNode($settings);

    $this->dsSelectLayout(array(), array(), 'admin/structure/comment/manage/comment/display');

    $fields = array(
      'fields[comment_title][region]' => 'left',
      'fields[comment_body][region]' => 'left',
    );
    $this->dsConfigureUI($fields, 'admin/structure/comment/manage/comment/display');

    // Post comment
    $comment1 = $this->postComment($node, $this->randomMachineName(), $this->randomMachineName());
    $this->assertRaw($comment1->comment_body->value, 'Comment1 found.');

    // Post comment
    $comment2 = $this->postComment($node, $this->randomMachineName(), $this->randomMachineName());
    $this->assertRaw($comment2->comment_body->value, 'Comment2 found.');

    // Verify there are no double ID's
    $xpath = $this->xpath('//a[@id="comment-1"]');
    $this->assertEqual(count($xpath), 1, '1 ID found named comment-1');
  }

  /**
   * Test User custom display on a comment on a node
   */
  public function testCommentUser() {
    // Create a node.
    $settings = array('type' => 'article', 'promote' => 1);
    $node = $this->drupalCreateNode($settings);

    // User compact display settings
    $this->dsSelectLayout(array(), array(), 'admin/config/people/accounts/display');

    $fields = array(
      'fields[username][region]' => 'left',
      'fields[member_for][region]' => 'left',
    );
    $this->dsConfigureUI($fields, 'admin/config/people/accounts/display');

    // Comment display settings
    $this->dsSelectLayout(array(), array(), 'admin/structure/comment/manage/comment/display');

    $fields = array(
      'fields[comment_title][region]' => 'left',
      'fields[comment_user][region]' => 'left',
      'fields[comment_body][region]' => 'left',
    );
    $this->dsConfigureUI($fields, 'admin/structure/comment/manage/comment/display');

    // Post comment
    $comment = $this->postComment($node, $this->randomMachineName(), $this->randomMachineName());
    $this->assertRaw($comment->comment_body->value, 'Comment found.');
    $this->assertRaw('Member for', 'Comment Member for found.');
    $xpath = $this->xpath('//div[@class="field field--name-comment-user field--type-ds field--label-hidden field__item"]/div/div/div[@class="field field--name-username field--type-ds field--label-hidden field__item"]');
    $this->assertEqual(count($xpath), 1, 'Username');
  }
}
