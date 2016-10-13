<?php

namespace Drupal\yamlform\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Tests for form date elements.
 *
 * @group YamlForm
 */
class YamlFormElementDateTest extends WebTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['yamlform', 'yamlform_test'];

  /**
   * Test date element.
   */
  public function testDateElement() {

    // Check date #max validation.
    $edit = ['date_range' => '2010-08-18'];
    $this->drupalPostForm('yamlform/test_element_dates', $edit, t('Submit'));
    $this->assertRaw('<em class="placeholder">date range (min/max)</em> must be on or before <em class="placeholder">2009-12-31</em>.');

    // Check date #mix validation.
    $edit = ['date_range' => '2006-08-18'];
    $this->drupalPostForm('yamlform/test_element_dates', $edit, t('Submit'));
    $this->assertRaw('<em class="placeholder">date range (min/max)</em> must be on or after <em class="placeholder">2009-01-01</em>.');

    // Check dynamic date.
    $this->drupalGet('yamlform/test_element_dates');
    $min = \Drupal::service('date.formatter')->format(strtotime('-1 year'), 'html_date');
    $max = \Drupal::service('date.formatter')->format(strtotime('+1 year'), 'html_date');
    $default_value = \Drupal::service('date.formatter')->format(strtotime('now'), 'html_date');
    $this->assertRaw('<input type="date" data-drupal-selector="edit-date-range-dynamic" aria-describedby="edit-date-range-dynamic--description" data-drupal-date-format="Y-m-d" id="edit-date-range-dynamic" name="date_range_dynamic" min="' . $min . '" max="' . $max . '" value="' . $default_value . '" class="form-date" />');
  }

}
