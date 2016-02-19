<?php

/**
 * @file
 * Contains \Drupal\panelizer\Tests\PanelizerNodeFunctionalTest.
 */

namespace Drupal\panelizer\Tests;

use Drupal\block_content\Entity\BlockContent;
use Drupal\simpletest\WebTestBase;

/**
 * Basic functional tests of using Panelizer with nodes.
 *
 * @group panelizer
 */
class PanelizerNodeFunctionalTest extends WebTestBase {

  /**
   * {@inheritdoc}
   */
  protected $profile = 'standard';

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'block',
    'ctools',
    'ctools_block',
    'layout_plugin',
    'node',
    'panelizer',
    'panelizer_test',
    'panels',
    'panels_ipe',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $user = $this->drupalCreateUser([
      'administer node display',
      'administer nodes',
      'administer content types',
      'create page content',
      'administer panelizer',
      'access panels in-place editing',
      'use panels in place editing',
    ]);
    $this->drupalLogin($user);

    $this->drupalPostForm('admin/structure/types/manage/page/display', [
      'panelizer[enable]' => TRUE,
      'panelizer[custom]' => TRUE,
    ], 'Save');
  }

  /**
   * Tests rendering a node with Panelizer default.
   */
  public function testPanelizerDefault() {
    /** @var \Drupal\panelizer\PanelizerInterface $panelizer */
    $panelizer = \Drupal::service('panelizer');
    $displays = $panelizer->getDefaultPanelsDisplays('node', 'page', 'default');
    $display = $displays['default'];
    $display->addBlock([
      'id' => 'panelizer_test',
      'label' => 'Panelizer test',
      'provider' => 'block_content',
      'region' => 'middle',
    ]);
    $panelizer->setDefaultPanelsDisplay('default', 'node', 'page', 'default', $display);

    // Create a node, and check that the IPE is visible on it.
    $node = $this->drupalCreateNode(['type' => 'page']);
    $out = $this->drupalGet('node/' . $node->id());
    $this->verbose($out);
    $elements = $this->xpath('//*[@id="panels-ipe-content"]');
    if (is_array($elements)) {
      $this->assertIdentical(count($elements), 1);
    }
    else {
      $this->fail('Could not parse page content.');
    }

    // Check that the block we added is visible.
    $this->assertText('Panelizer test');
    $this->assertText('Abracadabra');
  }

}
