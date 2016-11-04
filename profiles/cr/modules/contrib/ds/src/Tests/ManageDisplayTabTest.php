<?php

namespace Drupal\ds\Tests;

/**
 * Tests for the manage display tab in Display Suite.
 *
 * @group ds
 */
class ManageDisplayTabTest extends FastTestBase {

  /**
   * Test tabs.
   */
  public function testFieldPlugin() {
    /* @var \Drupal\node\NodeInterface $node */
    $node = $this->entitiesTestSetup();

    // Verify we can see the manage display tab on a node and can click on it.
    $this->drupalGet('node/' . $node->id());
    $this->assertRaw('Manage display', 'Manage display tab title found on node');
    $this->assertRaw('node/' . $node->id() . '/manage_display', 'Manage display tab link found on node');
    $this->drupalGet('node/' . $node->id() . '/manage_display');

    // Verify we can see the manage display tab on a user and can click on it.
    $this->drupalGet('user/' . $this->adminUser->id());
    $this->assertRaw('Manage display', 'Manage display tab title found on user');
    $this->assertRaw('user/' . $this->adminUser->id() . '/manage_display', 'Manage display tab link found on user');
    $this->drupalGet('user/' . $this->adminUser->id() . '/manage_display');

    // Verify we can see the manage display tab on a taxonomy term and can click
    // on it.
    $this->drupalGet('taxonomy/term/1');
    $this->assertRaw('Manage display', 'Manage display,title tab found on term');
    $this->assertRaw('taxonomy/term/1/manage_display', 'Manage display tab link found on term');
    $this->drupalGet('taxonomy/term/1/manage_display');
  }

}
