<?php

/**
 * @file
 * Contains Drupal\pathauto\Tests\AliasType\NodeAliasTest
 */

namespace Drupal\pathauto\Tests\AliasType;

use Drupal\simpletest\KernelTestBase;

/**
 * Tests the node alias plugin.
 *
 * @group pathauto
 */
class NodeAliasTest extends KernelTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('pathauto','path', 'node', 'user', 'token');

  /**
   *
   */
  public function testNodeAlias() {
    /** @var \Drupal\pathauto\AliasTypeManager $manager */
    $manager = $this->container->get('plugin.manager.alias_type');

    /** @var \Drupal\pathauto\AliasTypeInterface $node_type */
    $node_type = $manager->createInstance('node');

    $patterns = $node_type->getPatterns();
    $this->assertTrue((array_key_exists('node', $patterns)), "Node pattern exists.");
    $this->assertEqual($patterns['node'], 'Pattern for all Content paths', "Node pattern description matches.");

    $token_types = $node_type->getTokenTypes();
    $this->assertTrue(in_array('node', $token_types), "Node token type exists.");

    $label = $node_type->getLabel();
    $this->assertEqual($label, 'Content', "Plugin label matches.");

    $default_config = $node_type->defaultConfiguration();

    $this->assertTrue(array_key_exists('default', $default_config), "Default key exists.");
    $this->assertEqual($default_config['default'][0], '/content/[node:title]', "Default content pattern matches.");

  }

}
