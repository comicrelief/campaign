<?php

namespace Drupal\yamlform\Tests;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Session\AccountInterface;
use Drupal\user\Entity\User;
use Drupal\yamlform\Entity\YamlForm;
use Drupal\yamlform\Utility\YamlFormElementHelper;

/**
 * Tests for form submission form element.
 *
 * @group YamlForm
 */
class YamlFormSubmissionFormElementTest extends YamlFormTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['system', 'block', 'filter', 'node', 'user', 'yamlform', 'yamlform_test'];

  /**
   * Tests elements.
   */
  public function testElements() {
    global $base_path;

    /* Test #unique element property */

    $this->drupalLogin($this->adminFormUser);

    $yamlform_unique = YamlForm::load('test_element_unique');

    // Check element with #unique property only allows one unique 'value' to be
    // submitted.
    $sid = $this->postSubmission($yamlform_unique, [], t('Submit'));
    $this->assertNoRaw('The value <em class="placeholder">value</em> has already been submitted once for the <em class="placeholder">textfield</em> field. You may have already submitted this form, or you need to use a different value.');
    $this->drupalPostForm('yamlform/test_element_unique', [], t('Submit'));
    $this->assertRaw('The value <em class="placeholder">value</em> has already been submitted once for the <em class="placeholder">textfield</em> field. You may have already submitted this form, or you need to use a different value.');

    // Check element with #unique can be updated.
    $this->drupalPostForm("admin/structure/yamlform/manage/test_element_unique/submission/$sid/edit", [], t('Submit'));
    $this->assertNoRaw('The value <em class="placeholder">value</em> has already been submitted once for the <em class="placeholder">textfield</em> field. You may have already submitted this form, or you need to use a different value.');
    // @todo Determine why test_element_unique is not updating correctly during
    // testing.
    // $this->assertRaw('Submission updated in <em class="placeholder">Test: Element: Unique</em>.');

    /* Test invalid elements */

    // Check invalid elements .
    $this->drupalGet('yamlform/test_element_invalid');
    $this->assertRaw('Unable to display this form. Please contact the site administrator.');

    /* Test ignored properties */

    // Check ignored properties.
    $yamlform_ignored_properties = YamlForm::load('test_element_ignored_properties');
    $elements = $yamlform_ignored_properties->getElementsInitialized();
    foreach (YamlFormElementHelper::$ignoredProperties as $ignored_property) {
      $this->assert(!isset($elements['test'][$ignored_property]), new FormattableMarkup('@property ignored.', ['@property' => $ignored_property]));
    }

    /* Test #private element property */

    // Check element with #private property hidden for normal user.
    $this->drupalLogin($this->normalUser);
    $this->drupalGet('yamlform/test_element_private');
    $this->assertNoFieldByName('private', '');

    // Check element with #private property visible for admin user.
    $this->drupalLogin($this->adminFormUser);
    $this->drupalGet('yamlform/test_element_private');
    $this->assertFieldByName('private', '');

    /* Test #autocomplete_options element property */

    // Check routes data-drupal-selector.
    $this->drupalGet('yamlform/test_element_text_autocomplete');
    $this->assertRaw('<input data-drupal-selector="edit-autocomplete-options" class="form-autocomplete form-text" data-autocomplete-path="' . $base_path . 'yamlform/test_element_text_autocomplete/autocomplete/autocomplete_options" type="text" id="edit-autocomplete-options" name="autocomplete_options" value="" size="60" maxlength="255" />');

    // Check #autocomplete_options partial match.
    $this->drupalGet('yamlform/test_element_text_autocomplete/autocomplete/autocomplete_options', ['query' => ['q' => 'United']]);
    $this->assertRaw('[{"value":"United Arab Emirates","label":"United Arab Emirates"},{"value":"United Kingdom of Great Britain and N. Ireland","label":"United Kingdom of Great Britain and N. Ireland"},{"value":"United States Minor Outlying Islands","label":"United States Minor Outlying Islands"},{"value":"United States of America","label":"United States of America"}]');

    // Check #autocomplete_options exact match.
    $this->drupalGet('yamlform/test_element_text_autocomplete/autocomplete/autocomplete_options', ['query' => ['q' => 'United States of America']]);
    $this->assertRaw('[{"value":"United States of America","label":"United States of America"}]');

    // Check #autocompleteoptions just one character.
    $this->drupalGet('yamlform/test_element_text_autocomplete/autocomplete/autocomplete_options', ['query' => ['q' => 'U']]);
    $this->assertRaw('[{"value":"Anguilla","label":"Anguilla"},{"value":"Antigua and Barbuda","label":"Antigua and Barbuda"},{"value":"Aruba","label":"Aruba"},{"value":"Australia","label":"Australia"},{"value":"Austria","label":"Austria"}]');

    /* Test #autocomplete_existing element property */

    // Check autocomplete is not enabled until there is submission.
    $this->drupalGet('yamlform/test_element_text_autocomplete');
    $this->assertNoRaw('<input data-drupal-selector="edit-autocomplete-existing" class="form-autocomplete form-text" data-autocomplete-path="' . $base_path . 'yamlform/test_element_text_autocomplete/autocomplete/autocomplete_existing" type="text" id="edit-autocomplete-existing" name="autocomplete_existing" value="" size="60" maxlength="255" />');
    $this->assertRaw('<input data-drupal-selector="edit-autocomplete-existing" type="text" id="edit-autocomplete-existing" name="autocomplete_existing" value="" size="60" maxlength="255" class="form-text" />');

    // Check #autocomplete_existing no match.
    $this->drupalGet('yamlform/test_element_text_autocomplete/autocomplete/autocomplete_existing', ['query' => ['q' => 'abc']]);
    $this->assertRaw('[]');

    // Add #autocomplete_existing values to the submission table.
    $this->drupalPostForm('yamlform/test_element_text_autocomplete', ['autocomplete_existing' => 'abcdefg'], t('Submit'));

    // Check autocomplete enabled now that there is submisssion.
    $this->drupalGet('yamlform/test_element_text_autocomplete');
    $this->assertRaw('<input data-drupal-selector="edit-autocomplete-existing" class="form-autocomplete form-text" data-autocomplete-path="' . $base_path . 'yamlform/test_element_text_autocomplete/autocomplete/autocomplete_existing" type="text" id="edit-autocomplete-existing" name="autocomplete_existing" value="" size="60" maxlength="255" />');
    $this->assertNoRaw('<input data-drupal-selector="edit-autocomplete-existing" type="text" id="edit-autocomplete-existing" name="autocomplete_existing" value="" size="60" maxlength="255" class="form-text" />');

    // Check #autocomplete_existing match.
    $this->drupalGet('yamlform/test_element_text_autocomplete/autocomplete/autocomplete_existing', ['query' => ['q' => 'abc']]);
    $this->assertNoRaw('[]');
    $this->assertRaw('[{"value":"abcdefg","label":"abcdefg"}]');

    // Check #autocomplete_existing minimum number of characters < 3.
    $this->drupalGet('yamlform/test_element_text_autocomplete/autocomplete/autocomplete_existing', ['query' => ['q' => 'ab']]);
    $this->assertRaw('[]');
    $this->assertNoRaw('[{"value":"abcdefg","label":"abcdefg"}]');

    /* Test data elements */

    $yamlform_dates = YamlForm::load('test_element_dates');

    // Check '#format' values.
    $this->drupalGet('yamlform/test_element_dates');
    $this->assertFieldByName('date_default', '2009-08-18');
    $this->assertFieldByName('datetime_default[date]', '2009-08-18');
    $this->assertFieldByName('datetime_default[time]', '16:00:00');
    $this->assertFieldByName('datelist_default[month]', '8');

    // Check 'datelist' and 'datetime' #default_value.
    $form = $yamlform_dates->getSubmissionForm();
    $this->assert(is_string($form['elements']['date_elements']['date_default']['#default_value']), 'date_default #default_value is a string.');
    $this->assert($form['elements']['datetime_elements']['datetime_default']['#default_value'] instanceof DrupalDateTime, 'datelist_default #default_value instance of \Drupal\Core\Datetime\DrupalDateTime.');
    $this->assert($form['elements']['datelist_elements']['datelist_default']['#default_value'] instanceof DrupalDateTime, 'datelist_default #default_value instance of \Drupal\Core\Datetime\DrupalDateTime.');

    // Check 'entity_autocomplete' #default_value.
    $yamlform_entity_autocomplete = YamlForm::load('test_element_entity_reference');

    /* Test entity_autocomplete element */

    $this->drupalGet('yamlform/test_element_entity_reference');
    $this->assertFieldByName('entity_autocomplete_user_default', 'admin (1)');

    // Issue #2471154 Anonymous user label can't be viewed and auth user labels
    // are only accessible with 'access user profiles' permission.
    // https://www.drupal.org/node/2471154
    // Check if 'view label' access for accounts is supported (8.2.x+).
    if (User::load(0)->access('view label')) {
      $this->assertFieldByName('entity_autocomplete_user_tags', 'Anonymous (0), admin (1)');
    }
    else {
      $this->assertFieldByName('entity_autocomplete_user_tags', '- Restricted access - (0), admin (1)');
    }

    $form = $yamlform_entity_autocomplete->getSubmissionForm();

    // Single entity (w/o #tags).
    // TODO: (TESTING) Figure out why the below #default_value is an array when it should be the entity.
    // @see \Drupal\yamlform\YamlFormSubmissionForm::prepareElements()
    $this->assert($form['elements']['entity_autocomplete']['entity_autocomplete_user_default']['#default_value'][0] instanceof AccountInterface, 'user #default_value instance of \Drupal\Core\Session\AccountInterface.');

    // Multiple entities (w #tags).
    $this->assert($form['elements']['entity_autocomplete']['entity_autocomplete_user_tags']['#default_value'][0] instanceof AccountInterface, 'users #default_value instance of \Drupal\Core\Session\AccountInterface.');
    $this->assert($form['elements']['entity_autocomplete']['entity_autocomplete_user_tags']['#default_value'][1] instanceof AccountInterface, 'users #default_value instance of \Drupal\Core\Session\AccountInterface.');

    /* Test text format element */

    $yamlform_text_format = YamlForm::load('test_element_text_format');

    // Check 'text_format' values.
    $this->drupalGet('yamlform/test_element_text_format');
    $this->assertFieldByName('text_format[value]', 'The quick brown fox jumped over the lazy dog.');
    $this->assertRaw('No HTML tags allowed.');

    $text_format = [
      'value' => 'Custom value',
      'format' => 'custom_format',
    ];
    $form = $yamlform_text_format->getSubmissionForm(['data' => ['text_format' => $text_format]]);
    $this->assertEqual($form['elements']['text_format']['#default_value'], $text_format['value']);
    $this->assertEqual($form['elements']['text_format']['#format'], $text_format['format']);

    // Check elements properties moved to the form.
    $this->drupalGet('yamlform/test_form_properties');
    $this->assertPattern('/Form prefix<form /');
    $this->assertPattern('/<\/form>\s+Form suffix/');
    $this->assertRaw('form class="yamlform-submission-test-form-properties-form yamlform-submission-form test-form-properties yamlform-details-toggle" invalid="invalid" style="border: 10px solid red; padding: 1em;"');
  }

}
