<?php

namespace Drupal\ds\Tests;

use Drupal\field_group\Tests\FieldGroupTestTrait;

/**
 * Tests for field group integration with Display Suite.
 *
 * @group ds
 */
class FieldGroupTest extends FastTestBase {

  use FieldGroupTestTrait;

  /**
   * Test tabs.
   */
  public function testFieldPlugin() {
    // Create a node.
    $settings = array('type' => 'article', 'promote' => 1);
    /* @var \Drupal\node\NodeInterface $node */
    $node = $this->drupalCreateNode($settings);

    // Configure layout.
    $layout = array(
      'layout' => 'ds_2col',
    );
    $layout_assert = array(
      'regions' => array(
        'left' => '<td colspan="8">' . t('Left') . '</td>',
        'right' => '<td colspan="8">' . t('Right') . '</td>',
      ),
    );
    $this->dsSelectLayout($layout, $layout_assert);

    $data = array(
      'weight' => '1',
      'label' => 'Link',
      'format_type' => 'html_element',
      'format_settings' => array(
        'label' => 'Link',
        'element' => 'div',
        'id' => 'wrapper-id',
        'classes' => 'test-class',
      ),
    );
    $group = $this->createGroup('node', 'article', 'view', 'default', $data);

    $fields = array(
      'fields[' . $group->group_name . '][region]' => 'right',
      'fields[body][region]' => 'right',
    );
    $this->dsConfigureUi($fields);

    $fields = array(
      'fields[body][parent]' => $group->group_name,
    );
    $this->dsConfigureUi($fields);

    $this->drupalGet('node/' . $node->id());

    // Test group ids and classes.
    $this->assertFieldByXPath("//div[contains(@class, 'group-right')]/div[contains(@id, 'wrapper-id')]", NULL, 'Wrapper id set on wrapper div');
    $this->assertFieldByXPath("//div[contains(@class, 'group-right')]/div[contains(@class, 'test-class')]", NULL, 'Test class set on wrapper div' . 'class="' . $group->group_name . ' test-class');
  }

}
