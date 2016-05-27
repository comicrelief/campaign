<?php

/**
 * @file
 * Contains \Drupal\ds\Tests\ManageDisplayTabTest.
 */

namespace Drupal\ds\Tests;

/**
 * Tests for the manage display tab in Display Suite.
 *
 * @group ds
 */
class ManageDisplayTabTest extends FastTestBase {

  /**
   * Test tabs
   */
  function testFieldPlugin() {
    /** @var \Drupal\node\NodeInterface $node */
    $node = $this->entitiesTestSetup();

    // Verify we can see the manage display tab on a node and can click on it
    $this->drupalGet('node/' . $node->id());
    $this->assertRaw('Manage display', 'Manage display tab title found on node');
    $this->assertRaw('node/' . $node->id() . '/display', 'Manage display tab link found on node');
    // @todo figure out why this crashes
    //$this->drupalGet('node/' . $node->id() . '/display');

    // Verify we can see the manage display tab on a user and can click on it
    $this->drupalGet('user/' . $this->adminUser->id());
    $this->assertRaw('Manage display', 'Manage display tab title found on user');
    $this->assertRaw('user/' . $this->adminUser->id() . '/display', 'Manage display tab link found on user');
    // @todo figure out why this crashes
    //$this->drupalGet('user/' .  $this->adminUser->id() . '/display');

    // Verify we can see the manage display tab on a taxonomy term and can click on it
    $this->drupalGet('taxonomy/term/1');
    $this->assertRaw('Manage display', 'Manage display,title tab found on term');
    $this->assertRaw('taxonomy/term/1/display', 'Manage display tab link found on term');
    // @todo figure out why this crashes
    //$this->drupalGet('taxonomy/term/1/display');

  }

}
