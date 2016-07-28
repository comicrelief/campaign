<?php

/**
 * @file
 * Definition of Drupal\yamlform\test\YamlFormNodeTest.
 */

namespace Drupal\yamlform\Tests;

/**
 * Tests for YAML form node.
 *
 * @group YamlForm
 */
class YamlFormNodeTest extends YamlFormTestBase {

  /**
   * Tests YAML form node.
   */
  public function testNode() {
    // Create node.
    $node = $this->drupalCreateNode(['type' => 'yamlform']);

    // Check contact form.
    $node->yamlform->target_id = 'contact';
    $node->yamlform->status = 1;
    $node->save();
    $this->drupalGet('node/' . $node->id());
    $this->assertRaw('yamlform-submission-contact-form');
    $this->assertNoFieldByName('name', 'John Smith');

    // Check contact form with default data.
    $node->yamlform->default_data = "name: 'John Smith'";
    $node->save();
    $this->drupalGet('node/' . $node->id());
    $this->assertFieldByName('name', 'John Smith');

    // Check contact form closed.
    $node->yamlform->status = 0;
    $node->save();
    $this->drupalGet('node/' . $node->id());
    $this->assertNoFieldByName('name', 'John Smith');
    $this->assertRaw('Sorry...This form is closed to new submissions.');

    // Check confirmation inline form.
    $node->yamlform->target_id = 'test_confirmation_inline';
    $node->yamlform->status = 1;
    $node->save();
    $this->drupalPostForm('node/' . $node->id(), [], t('Submit'));
    $this->assertRaw('This is a custom inline confirmation message.');
  }

}
