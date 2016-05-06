<?php

/**
 * @file
 * Definition of Drupal\yamlform\test\YamlSubmissionFormInputsTest.
 */

namespace Drupal\yamlform\Tests;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Session\AccountInterface;
use Drupal\user\Entity\User;
use Drupal\yamlform\Entity\YamlForm;

/**
 * Tests for YAML form submission form inputs.
 *
 * @group YamlForm
 */
class YamlFormSubmissionFormInputsTest extends YamlFormTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['system', 'block', 'filter', 'node', 'user', 'yamlform', 'yamlform_test'];

  /**
   * Tests prepare inputs.
   */
  public function testPrepareInputs() {
    // Check invalid inputs.
    $this->drupalGet('yamlform/test_inputs_invalid');
    $this->assertRaw('Unable to display this form. Please contact the site administrator.');

    // Check ignored properties.
    $yamlform_confirmation_inline = YamlForm::load('test_inputs_ignored_properties');
    $inputs = $yamlform_confirmation_inline->getInputs();
    foreach (YamlForm::getIgnoredProperties() as $ignored_property) {
      $this->assert(!isset($inputs['test'][$ignored_property]), new FormattableMarkup('@property ignored.', ['@property' => $ignored_property]));
    }

    // Check input with #private property hidden for normal user.
    $this->drupalLogin($this->normalUser);
    $this->drupalGet('yamlform/test_inputs_private');
    $this->assertNoFieldByName('private', '');

    // Check input with #private property visible for admin user.
    $this->drupalLogin($this->adminFormUser);
    $this->drupalGet('yamlform/test_inputs_private');
    $this->assertFieldByName('private', '');

    /* Test data elements */
    // TODO: (TESTING) Check element types.

    $yamlform_inputs_dates = YamlForm::load('test_inputs_dates');

    // Check 'text_format' values.
    $this->drupalGet('yamlform/test_inputs_dates');
    $this->assertFieldByName('date', '2009-08-18');
    $this->assertFieldByName('datetime[date]', '2009-08-18');
    $this->assertFieldByName('datetime[time]', '16:00:00');
    $this->assertFieldByName('datelist_date[month]', '8');

    // Check 'datelist' and 'datetime' #default_value.
    $form = $yamlform_inputs_dates->getSubmissionForm();
    $this->assert(is_string($form['inputs']['date']['#default_value']), 'date #default_value is a string.');
    $this->assert($form['inputs']['datetime']['#default_value'] instanceof DrupalDateTime, 'datelist_date #default_value instance of \Drupal\Core\Datetime\DrupalDateTime.');
    $this->assert($form['inputs']['datelist_date']['#default_value'] instanceof DrupalDateTime, 'datelist_date #default_value instance of \Drupal\Core\Datetime\DrupalDateTime.');

    // Check 'entity_autocomplete' #default_value.
    $yamlform_inputs_entity_autocomplete = YamlForm::load('test_inputs_entity_autocomplete');

    /* Test entity_autocomplete element */

    $this->drupalGet('yamlform/test_inputs_entity_autocomplete');
    $this->assertFieldByName('user', 'admin (1)');

    // Issue #2471154 Anonymous user label can't be viewed and auth user labels
    // are only accessible with 'access user profiles' permission.
    // https://www.drupal.org/node/2471154
    // Check if 'view label' access for accounts is supported (8.2.x+).
    if (User::load(0)->access('view label')) {
      $this->assertFieldByName('users', 'Anonymous (0), admin (1)');
    }
    else {
      $this->assertFieldByName('users', '- Restricted access - (0), admin (1)');
    }

    $form = $yamlform_inputs_entity_autocomplete->getSubmissionForm();

    // Single entity (w/o #tags).
    // TODO: (TESTING) Figure out why the below #default_value is an array when it should be the entity.
    // @see \Drupal\yamlform\YamlFormSubmissionForm::prepareInputs()
    $this->assert($form['inputs']['user']['#default_value'][0] instanceof AccountInterface, 'user #default_value instance of \Drupal\Core\Session\AccountInterface.');

    // Multiple entities (w #tags).
    $this->assert($form['inputs']['users']['#default_value'][0] instanceof AccountInterface, 'users #default_value instance of \Drupal\Core\Session\AccountInterface.');
    $this->assert($form['inputs']['users']['#default_value'][1] instanceof AccountInterface, 'users #default_value instance of \Drupal\Core\Session\AccountInterface.');

    /* Test text format element */

    $yamlform_test_inputs_text_format = YamlForm::load('test_inputs_text_format');

    // Check 'text_format' values.
    $this->drupalGet('yamlform/test_inputs_text_format');
    $this->assertFieldByName('text_format[value]', 'The quick brown fox jumped over the lazy dog.');
    $this->assertRaw('No HTML tags allowed.');

    $text_format = [
      'value' => 'Custom value',
      'format' => 'custom_format',
    ];
    $form = $yamlform_test_inputs_text_format->getSubmissionForm(['data' => ['text_format' => $text_format]]);
    $this->assertEqual($form['inputs']['text_format']['#default_value'], $text_format['value']);
    $this->assertEqual($form['inputs']['text_format']['#format'], $text_format['format']);
  }

}
