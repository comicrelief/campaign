<?php

namespace Drupal\yamlform\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Tests for form elements.
 *
 * @group YamlForm
 */
class YamlFormElementTest extends WebTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['yamlform', 'yamlform_test'];

  /**
   * Test element settings.
   */
  public function testElements() {

    /**************************************************************************/
    // Date
    /**************************************************************************/

    // Check date #max validation.
    $edit = ['date_range' => '2010-08-18'];
    $this->drupalPostForm('yamlform/test_element_dates', $edit, t('Submit'));
    $this->assertRaw('<em class="placeholder">date range (min/max)</em> must be on or before <em class="placeholder">2009-12-31</em>.');

    // Check date #mix validation.
    $edit = ['date_range' => '2006-08-18'];
    $this->drupalPostForm('yamlform/test_element_dates', $edit, t('Submit'));
    $this->assertRaw('<em class="placeholder">date range (min/max)</em> must be on or after <em class="placeholder">2009-01-01</em>.');

    /**************************************************************************/
    // Allowed tags
    /**************************************************************************/

    // Check <b> tags is allowed.
    $this->drupalGet('yamlform/test_element_allowed_tags');
    $this->assertRaw('Hello <b>...Goodbye</b>');

    // Check custom <ignored> <tag> is allowed and <b> tag removed.
    \Drupal::configFactory()->getEditable('yamlform.settings')
      ->set('elements.allowed_tags', 'ignored tag')
      ->save();
    $this->drupalGet('yamlform/test_element_allowed_tags');
    $this->assertRaw('Hello <ignored></tag>...Goodbye');

    // Restore admin tags.
    \Drupal::configFactory()->getEditable('yamlform.settings')
      ->set('elements.allowed_tags', 'admin')
      ->save();
  }

}
