<?php

namespace Drupal\yamlform\Tests;

/**
 * Tests for form block.
 *
 * @group YamlForm
 */
class YamlFormBlockTest extends YamlFormTestBase {

  /**
   * Tests form block.
   */
  public function testBlock() {
    // Place block.
    $block = $this->drupalPlaceBlock('yamlform_block');

    // Check contact form.
    $block->getPlugin()->setConfigurationValue('yamlform_id', 'contact');
    $block->save();
    $this->drupalGet('<front>');
    $this->assertRaw('yamlform-submission-contact-form');

    // Check contact form with default data.
    $block->getPlugin()->setConfigurationValue('default_data', "name: 'John Smith'");
    $block->save();
    $this->drupalGet('<front>');
    $this->assertRaw('yamlform-submission-contact-form');
    $this->assertFieldByName('name', 'John Smith');

    // Check confirmation inline form.
    $block->getPlugin()->setConfigurationValue('yamlform_id', 'test_confirmation_inline');
    $block->save();
    $this->drupalPostForm('<front>', [], t('Submit'));
    $this->assertRaw('This is a custom inline confirmation message.');

    // Check confirmation message form.
    $block->getPlugin()->setConfigurationValue('yamlform_id', 'test_confirmation_message');
    $block->save();
    $this->drupalPostForm('<front>', [], t('Submit'));
    $this->assertRaw('This is a <b>custom</b> confirmation message.');

  }

}
