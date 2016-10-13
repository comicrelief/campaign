<?php

namespace Drupal\yamlform\Tests;

use Drupal\node\Entity\Node;

/**
 * Tests the normalization of yamlform entity reference items.
 *
 * @group YamlForm
 */
class YamlFormEntityReferenceItemNormalizerTest extends YamlFormTestBase {
  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'system',
    'block',
    'node',
    'user',
    'yamlform',
    'yamlform_node',
    'yamlform_test',
    'hal',
    'serialization',
  ];

  /**
   * Tests the normalization of a node with a form entity reference.
   */
  public function testYamlFormEntityReferenceItemNormalization() {
    // Create node.
    $node = $this->drupalCreateNode(['type' => 'yamlform']);
    $yamlform_field = 'yamlform';

    // Set yamlform field to reference the contact form and add data.
    $node->{$yamlform_field}->target_id = 'contact';
    $node->{$yamlform_field}->default_data = 'name: Please enter your name\r\nemail: Please enter a valid email address';
    $node->{$yamlform_field}->status = 1;
    $node->save();

    // Normalize the node.
    $serializer = $this->container->get('serializer');
    $normalized = $serializer->normalize($node, 'hal_json');
    $this->assertEqual($node->{$yamlform_field}->default_data, $normalized[$yamlform_field][0]['default_data']);
    $this->assertEqual($node->{$yamlform_field}->status, $normalized[$yamlform_field][0]['status']);

    // Denormalize the node.
    $new_node = $serializer->denormalize($normalized, Node::class, 'hal_json');
    $this->assertEqual($node->{$yamlform_field}->default_data, $new_node->{$yamlform_field}->default_data);
    $this->assertEqual($node->{$yamlform_field}->status, $new_node->{$yamlform_field}->status);
  }

}
