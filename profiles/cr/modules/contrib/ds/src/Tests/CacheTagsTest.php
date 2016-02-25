<?php
/**
 * @file
 * Contains Drupal\ds\Tests\EntitiesTest.
 */

namespace Drupal\ds\Tests;

/**
 * Tests for cache tags associated with an entity.
 *
 * @group ds
 */
class CacheTagsTest extends FastTestBase {

  public function testUserCacheTags() {
    // Create a node.
    $settings = array('type' => 'article', 'promote' => 1);
    $node = $this->drupalCreateNode($settings);

    // Create field CSS classes.
    $edit = array('fields' => "test_field_class\ntest_field_class_2|Field class 2");
    $this->drupalPostForm('admin/structure/ds/classes', $edit, t('Save configuration'));

    // Create a token field.
    $token_field = array(
      'name' => 'Token field',
      'id' => 'token_field',
      'entities[node]' => '1',
      'content[value]' => '[node:title]',
    );
    $this->dsCreateTokenField($token_field);

    // Select layout.
    $this->dsSelectLayout();

    // Configure fields.
    $fields = array(
      'fields[dynamic_token_field:node-token_field][region]' => 'header',
      'fields[body][region]' => 'right',
      'fields[node_link][region]' => 'footer',
      'fields[body][label]' => 'above',
      'fields[node_submitted_by][region]' => 'header',
    );
    $this->dsConfigureUI($fields);

    $this->drupalGet('node/' . $node->id());
    $headers = $this->drupalGetHeader('X-Drupal-Cache-Tags');
    $this->assertTrue(
      strpos($headers,'user:' . $node->getRevisionAuthor()->getOriginalId()),
      'User cache tag found'
    );
  }
}
