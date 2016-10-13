<?php

namespace Drupal\yamlform\Tests;

use Drupal\simpletest\WebTestBase;
use Drupal\yamlform\Entity\YamlForm;

/**
 * Tests for form (render) element extras.
 *
 * @group YamlForm
 */
class YamlFormElementExtrasTest extends WebTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['system', 'block', 'filter', 'node', 'user', 'yamlform', 'yamlform_test'];

  /**
   * Tests building of custom elements.
   */
  public function testBuildElements() {
    $this->drupalGet('yamlform/test_element_extras');

    /**************************************************************************/
    // text_format
    /**************************************************************************/

    // Check that formats and tips are removed and/or hidden.
    $this->assertRaw('<div class="filter-wrapper js-form-wrapper form-wrapper" data-drupal-selector="edit-text-format-format" style="display: none" id="edit-text-format-format">');
    $this->assertRaw('<div class="filter-help js-form-wrapper form-wrapper" data-drupal-selector="edit-text-format-format-help" style="display: none" id="edit-text-format-format-help">');

    /**************************************************************************/
    // counter
    /**************************************************************************/

    $this->assertRaw('<input data-counter-type="character" data-counter-limit="10" class="js-yamlform-counter yamlform-counter form-text" data-drupal-selector="edit-counter-characters" type="text" id="edit-counter-characters" name="counter_characters" value="" size="60" maxlength="255" />');
    $this->assertRaw('<textarea data-counter-type="word" data-counter-limit="3" data-counter-message="word(s) left. This is a custom message" class="js-yamlform-counter yamlform-counter form-textarea resize-vertical" data-drupal-selector="edit-counter-words" id="edit-counter-words" name="counter_words" rows="5" cols="60"></textarea>');

    /**************************************************************************/
    // creditcard_number
    /**************************************************************************/

    // Check basic creditcard_number.
    $this->assertRaw('<label for="edit-creditcard-number-basic">Credit card number basic</label>');
    $this->assertRaw('<input data-drupal-selector="edit-creditcard-number-basic" type="text" id="edit-creditcard-number-basic" name="creditcard_number_basic" value="" size="16" maxlength="16" class="form-textfield form-creditcard-number" />');

    /**************************************************************************/
    // email_multiple
    /**************************************************************************/

    // Check basic email_multiple.
    $this->assertRaw('<label for="edit-email-multiple-basic">Multiple email addresses (basic)</label>');
    $this->assertRaw('<input data-drupal-selector="edit-email-multiple-basic" aria-describedby="edit-email-multiple-basic--description" type="text" id="edit-email-multiple-basic" name="email_multiple_basic" value="" size="60" class="form-textfield form-email-multiple" />');
    $this->assertRaw('Multiple email addresses may be separated by commas.');

    /**************************************************************************/
    // email_confirm
    /**************************************************************************/

    // Check basic email_confirm.
    $this->assertRaw('<div data-drupal-selector="edit-email-confirm-basic" id="edit-email-confirm-basic" class="js-form-wrapper form-wrapper">');
    $this->assertRaw('<div class="js-form-item form-item js-form-type-email form-type-email js-form-item-email-confirm-basic-mail-1 form-item-email-confirm-basic-mail-1">');
    $this->assertRaw('<label for="edit-email-confirm-basic-mail-1">Email confirm basic</label>');
    $this->assertRaw('<input data-drupal-selector="edit-email-confirm-basic-mail-1" class="yamlform-email form-email" type="email" id="edit-email-confirm-basic-mail-1" name="email_confirm_basic[mail_1]" value="" size="60" maxlength="254" />');
    $this->assertRaw('<div class="js-form-item form-item js-form-type-email form-type-email js-form-item-email-confirm-basic-mail-2 form-item-email-confirm-basic-mail-2">');
    $this->assertRaw('<label for="edit-email-confirm-basic-mail-2">Confirm email</label>');
    $this->assertRaw('<input data-drupal-selector="edit-email-confirm-basic-mail-2" class="yamlform-email-confirm form-email" type="email" id="edit-email-confirm-basic-mail-2" name="email_confirm_basic[mail_2]" value="" size="60" maxlength="254" />');

    // Check advanced email_confirm w/ custom label.
    $this->assertRaw('<div data-drupal-selector="edit-email-confirm-advanced" aria-describedby="edit-email-confirm-advanced--description" id="edit-email-confirm-advanced" class="js-form-wrapper form-wrapper">');
    $this->assertRaw('<div class="js-form-item form-item js-form-type-email form-type-email js-form-item-email-confirm-advanced-mail-1 form-item-email-confirm-advanced-mail-1">');
    $this->assertRaw('<label for="edit-email-confirm-advanced-mail-1">Email confirm advanced</label>');
    $this->assertRaw('<input data-drupal-selector="edit-email-confirm-advanced-mail-1" aria-describedby="edit-email-confirm-advanced-mail-1--description" class="yamlform-email form-email" type="email" id="edit-email-confirm-advanced-mail-1" name="email_confirm_advanced[mail_1]" value="" size="60" maxlength="254" placeholder="Enter email address" />');
    $this->assertRaw('<div id="edit-email-confirm-advanced-mail-1--description" class="description">');
    $this->assertRaw('Please make sure to review your email address');
    $this->assertRaw('<div class="js-form-item form-item js-form-type-email form-type-email js-form-item-email-confirm-advanced-mail-2 form-item-email-confirm-advanced-mail-2">');
    $this->assertRaw('<label for="edit-email-confirm-advanced-mail-2">Please confirm your email address</label>');
    $this->assertRaw('<input data-drupal-selector="edit-email-confirm-advanced-mail-2" aria-describedby="edit-email-confirm-advanced-mail-2--description" class="yamlform-email-confirm form-email" type="email" id="edit-email-confirm-advanced-mail-2" name="email_confirm_advanced[mail_2]" value="" size="60" maxlength="254" placeholder="Enter confirmation email address" />');
    $this->assertRaw('<div id="edit-email-confirm-advanced-mail-2--description" class="description">');
    $this->assertRaw('Please make sure to review your confirmation email address');

    /**************************************************************************/
    // signature
    /**************************************************************************/

    $this->assertRaw('<input data-drupal-selector="edit-signature-basic" aria-describedby="edit-signature-basic--description" type="hidden" name="signature_basic" value="" class="js-yamlform-signature form-yamlform-signature" /><div class="js-yamlform-signature-pad yamlform-signature-pad">');
    $this->assertRaw('<input type="submit" name="op" value="Reset" class="button js-form-submit form-submit" />');
    $this->assertRaw('<canvas></canvas>');
    $this->assertRaw('</div>');
    $this->assertRaw('<div id="edit-signature-basic--description" class="description">');
    $this->assertRaw('Sign above');

    /**************************************************************************/
    // toggle
    /**************************************************************************/

    // Check basic toggle.
    $this->assertRaw('<div class="js-form-item form-item js-form-type-yamlform-toggle form-type-yamlform-toggle js-form-item-toggle-basic form-item-toggle-basic">');
    $this->assertRaw('<input data-drupal-selector="edit-toggle-basic" type="checkbox" id="edit-toggle-basic" name="toggle_basic" value="1" class="form-checkbox" />');
    $this->assertRaw('<div class="js-yamlform-toggle yamlform-toggle toggle toggle-medium toggle-light" data-toggle-height="24" data-toggle-width="48" data-toggle-text-on="" data-toggle-text-off=""></div>');
    $this->assertRaw('<label for="edit-toggle-basic" class="option">This is a basic toggle</label>');

    // Check advanced toggle.
    $this->assertRaw('<div class="js-form-item form-item js-form-type-yamlform-toggle form-type-yamlform-toggle js-form-item-toggle-advanced form-item-toggle-advanced">');
    $this->assertRaw('<label for="edit-toggle-advanced">This is an advanced toggle</label>');
    $this->assertRaw('<input data-drupal-selector="edit-toggle-advanced" type="checkbox" id="edit-toggle-advanced" name="toggle_advanced" value="1" class="form-checkbox" />');
    $this->assertRaw('<div class="js-yamlform-toggle yamlform-toggle toggle toggle-large toggle-iphone" data-toggle-height="36" data-toggle-width="108" data-toggle-text-on="Yes" data-toggle-text-off="No"></div>');

    /**************************************************************************/
    // rating
    /**************************************************************************/

    // Check basic.
    $this->assertRaw('<label for="edit-rating-basic">Rating basic</label>');
    $this->assertRaw('<input data-drupal-selector="edit-rating-basic" type="range" id="edit-rating-basic" name="rating_basic" value="0" step="1" min="0" max="5" class="form-yamlform-rating" />');
    $this->assertRaw('<div class="rateit svg rateit-medium" data-rateit-min="0" data-rateit-max="5" data-rateit-step="1" data-rateit-resetable="false" data-rateit-readonly="false" data-rateit-backingfld="#edit-rating-basic" data-rateit-value="" data-rateit-starheight="24" data-rateit-starwidth="24">');

    // Check advanced.
    $this->assertRaw('<label for="edit-rating-advanced">Rating advanced</label>');
    $this->assertRaw('<input data-drupal-selector="edit-rating-advanced" type="range" id="edit-rating-advanced" name="rating_advanced" value="0" step="0.1" min="0" max="10" class="form-yamlform-rating" />');
    $this->assertRaw('<div class="rateit svg rateit-large" data-rateit-min="0" data-rateit-max="10" data-rateit-step="0.1" data-rateit-resetable="true" data-rateit-readonly="false" data-rateit-backingfld="#edit-rating-advanced" data-rateit-value="" data-rateit-starheight="32" data-rateit-starwidth="32">');

    /**************************************************************************/
    // likert
    /**************************************************************************/

    $this->assertRaw('<table class="yamlform-likert-table responsive-enabled" data-likert-answers-count="3" data-drupal-selector="edit-likert-basic-table" id="edit-likert-basic-table" data-striping="1">');
    $this->assertPattern('#<th></th>\s+<th>Option 1</th>\s+<th>Option 2</th>\s+<th>Option 3</th>#');
    $this->assertRaw('<label for="edit-likert-basic-table-q1-question-title">Question 1</label>');
    $this->assertRaw('<td><div class="js-form-item form-item js-form-type-radio form-type-radio js-form-item-likert-basic-q1 form-item-likert-basic-q1">');
    $this->assertRaw('<input data-drupal-selector="edit-likert-basic-q1" type="radio" id="edit-likert-basic-q1" name="likert_basic[q1]" value="1" class="form-radio" />');
    $this->assertRaw('<label for="edit-likert-basic-q1" class="option">Option 1</label>');

    /**************************************************************************/
    // code:yaml
    /**************************************************************************/

    // Check YAML.
    $this->assertRaw('<label for="edit-yaml-basic">YAML basic</label>');
    $this->assertRaw('<textarea data-drupal-selector="edit-yaml-basic" class="js-yamlform-codemirror yamlform-codemirror yaml form-textarea resize-vertical" data-yamlform-codemirror-mode="text/x-yaml" id="edit-yaml-basic" name="yaml_basic" rows="5" cols="60"></textarea>');

    /**************************************************************************/
    // code:html
    /**************************************************************************/

    // Check HTML.
    $this->assertRaw('<label for="edit-html-basic">HTML basic</label>');
    $this->assertRaw('<textarea data-drupal-selector="edit-html-basic" class="js-yamlform-codemirror yamlform-codemirror html form-textarea resize-vertical" data-yamlform-codemirror-mode="text/html" id="edit-html-basic" name="html_basic" rows="5" cols="60"></textarea>');

    /**************************************************************************/
    // code:text
    /**************************************************************************/

    // Check Text.
    $this->assertRaw('<label for="edit-text-basic">Text basic</label>');
    $this->assertRaw('<textarea data-drupal-selector="edit-text-basic" class="js-yamlform-codemirror yamlform-codemirror text form-textarea resize-vertical" data-yamlform-codemirror-mode="text/plain" id="edit-text-basic" name="text_basic" rows="5" cols="60"></textarea>');

    /**************************************************************************/
    // contact (composite element)
    /**************************************************************************/

    // Check form contact basic.
    $this->assertRaw('<fieldset data-drupal-selector="edit-contact-basic" id="edit-contact-basic--wrapper" class="fieldgroup form-composite js-yamlform-contact yamlform-contact required js-form-item form-item js-form-wrapper form-wrapper" required="required" aria-required="true">');
    $this->assertRaw('<span class="fieldset-legend js-form-required form-required">Contact basic</span>');
    $this->assertRaw('<label for="edit-contact-basic-name" class="js-form-required form-required">Name</label>');
    $this->assertRaw('<input data-drupal-selector="edit-contact-basic-name" type="text" id="edit-contact-basic-name" name="contact_basic[name]" value="John Smith" size="60" maxlength="128" class="form-text required" required="required" aria-required="true" />');

    // Check custom name title, description, and required.
    $this->assertRaw('<label for="edit-contact-advanced-name" class="js-form-required form-required">Custom contact name</label>');
    $this->assertRaw('<input data-drupal-selector="edit-contact-advanced-name" aria-describedby="edit-contact-advanced-name--description" type="text" id="edit-contact-advanced-name" name="contact_advanced[name]" value="John Smith" size="60" maxlength="128" class="form-text required" required="required" aria-required="true" />');
    $this->assertRaw('Custom contact name description');

    // Check custom state type and not required.
    $this->assertRaw('<label for="edit-contact-advanced-state-province">State/Province</label>');
    $this->assertRaw('<input data-drupal-selector="edit-contact-advanced-state-province" type="text" id="edit-contact-advanced-state-province" name="contact_advanced[state_province]" value="New Jersey" size="60" maxlength="128" class="form-text" />');

    // Check custom country access.
    $this->assertNoRaw('edit-contact-advanced-country');

    /**************************************************************************/
    // creditcard (composite element)
    /**************************************************************************/

    $this->assertRaw('<fieldset data-drupal-selector="edit-creditcard" id="edit-creditcard--wrapper" class="fieldgroup form-composite js-yamlform-creditcard yamlform-creditcard js-form-item form-item js-form-wrapper form-wrapper">');
    $this->assertRaw('<span class="fieldset-legend">Credit Card</span>');
    $this->assertRaw('<div class="messages messages--warning">The credit card element is experimental and insecure because it stores submitted information as plain text.</div>');
    $this->assertRaw('<label for="edit-creditcard-name">Name on Card</label>');
    $this->assertRaw('<input data-drupal-selector="edit-creditcard-name" type="text" id="edit-creditcard-name" name="creditcard[name]" value="John Smith" size="60" maxlength="128" class="form-text" />');
    $this->assertRaw('<select data-drupal-selector="edit-creditcard-expiration-month" id="edit-creditcard-expiration-month" name="creditcard[expiration_month]" class="form-select">');

    /**************************************************************************/
    // table
    /**************************************************************************/

    $this->assertRaw('<table class="js-form-wrapper responsive-enabled" data-drupal-selector="edit-table" id="edit-table" data-striping="1">');
    $this->assertRaw('<th>First Name</th>');
    $this->assertRaw('<th>Last Name</th>');
    $this->assertRaw('<th>Gender</th>');
    $this->assertRaw('<tr data-drupal-selector="edit-table-1" class="odd">');
    $this->assertRaw('<td><div class="js-form-item form-item js-form-type-textfield form-type-textfield js-form-item-table__1__first-name form-item-table__1__first-name form-no-label">');
    $this->assertRaw('<input data-drupal-selector="edit-table-1-first-name" type="text" id="edit-table-1-first-name" name="table__1__first_name" value="John" size="20" maxlength="255" class="form-text" />');
  }

  /**
   * Tests value processing for custom elements.
   */
  public function testProcessingElements() {
    /** @var \Drupal\yamlform\YamlFormInterface $yamlform */
    $yamlform = YamlForm::load('test_element_extras');

    /**************************************************************************/
    // counter
    /**************************************************************************/

    // Check counter validation error.
    $edit = [
      'counter_characters' => '01234567890',
      'counter_words' => 'one two three four',
    ];
    $this->drupalPostForm('yamlform/test_element_extras', $edit, t('Submit'));
    $this->assertRaw('counter_characters</em> must be less than 10 characters.');
    $this->assertRaw('counter_words</em> must be less than 3 words.');

    // Check counter validation passes.
    $edit = [
      'counter_characters' => '0123456789',
      'counter_words' => 'one two three',
    ];
    $this->drupalPostForm('yamlform/test_element_extras', $edit, t('Submit'));
    $this->assertNoRaw('counter_characters</em> must be less than 10 characters.');
    $this->assertNoRaw('counter_words</em> must be less than 3 words.');

    /**************************************************************************/
    // table
    /**************************************************************************/

    $this->drupalPostForm('yamlform/test_element_extras', [], t('Submit'));
    $this->assertRaw('table__1__first_name: John');
    $this->assertRaw('table__1__last_name: Smith');
    $this->assertRaw('table__1__gender: Male');
    $this->assertRaw('table__2__first_name: Jane');
    $this->assertRaw('table__2__last_name: Doe');
    $this->assertRaw('table__2__gender: Female');

    /**************************************************************************/
    // creditcard_number
    /**************************************************************************/

    // Check invalid credit card number.
    $edit = [
      'creditcard_number_basic' => 'not value',
    ];
    $this->drupalPostForm('yamlform/test_element_extras', $edit, t('Submit'));
    $this->assertRaw('The credit card number is not valid.');

    // Check valid credit card number.
    $edit = [
      'creditcard_number_basic' => '4111111111111111',
    ];
    $this->drupalPostForm('yamlform/test_element_extras', $edit, t('Submit'));
    $this->assertNoRaw('The credit card number is not valid.');

    // Check valid AmEx (15 digit).
    $edit = [
      'creditcard_number_basic' => '378282246310005',
    ];
    $this->drupalPostForm('yamlform/test_element_extras', $edit, t('Submit'));
    $this->assertNoRaw('The credit card number is not valid.');

    /**************************************************************************/
    // email_multiple
    /**************************************************************************/

    // Check invalid second email address.
    $edit = [
      'email_multiple_basic' => 'example@example.com, Not a valid email address',
    ];
    $this->drupalPostForm('yamlform/test_element_extras', $edit, t('Submit'));
    $this->assertRaw('The email address <em class="placeholder">Not a valid email address</em> is not valid.');

    // Check invalid token email address.
    $edit = [
      'email_multiple_basic' => 'example@example.com, [token]',
    ];
    $this->drupalPostForm('yamlform/test_element_extras', $edit, t('Submit'));
    $this->assertRaw('The email address <em class="placeholder">[token]</em> is not valid.');

    // Check valid second email address.
    $edit = [
      'email_multiple_basic' => 'example@example.com, other@other.com',
    ];
    $this->drupalPostForm('yamlform/test_element_extras', $edit, t('Submit'));
    $this->assertRaw("email_multiple_basic: 'example@example.com, other@other.com'");

    // Check valid token email address (via #allow_tokens).
    $edit = [
      'email_multiple_advanced' => 'example@example.com, [token]',
    ];
    $this->drupalPostForm('yamlform/test_element_extras', $edit, t('Submit'));
    $this->assertRaw("email_multiple_advanced: 'example@example.com, [token]'");

    /**************************************************************************/
    // email_confirm
    /**************************************************************************/

    // Check invalid email addresses.
    $edit = [
      'email_confirm_advanced[mail_1]' => 'Not a valid email address',
      'email_confirm_advanced[mail_2]' => 'Not a valid email address, again',
    ];
    $this->drupalPostForm('yamlform/test_element_extras', $edit, t('Submit'));
    $this->assertRaw('The email address <em class="placeholder">Not a valid email address</em> is not valid.');
    $this->assertRaw('The email address <em class="placeholder">Not a valid email address, again</em> is not valid.');

    // Check non-matching email addresses.
    $edit = [
      'email_confirm_advanced[mail_1]' => 'example01@example.com',
      'email_confirm_advanced[mail_2]' => 'example02@example.com',
    ];
    $this->drupalPostForm('yamlform/test_element_extras', $edit, t('Submit'));
    $this->assertRaw('The specified email addresses do not match.');

    // Check matching email addresses.
    $edit = [
      'email_confirm_advanced[mail_1]' => 'example@example.com',
      'email_confirm_advanced[mail_2]' => 'example@example.com',
    ];
    $this->drupalPostForm('yamlform/test_element_extras', $edit, t('Submit'));
    $this->assertNoRaw('<li class="messages__item">The specified email addresses do not match.</li>');
    $this->assertRaw('email_confirm_advanced: example@example.com');

    // Check empty confirm email address.
    $edit = [
      'email_confirm_advanced[mail_1]' => '',
      'email_confirm_advanced[mail_2]' => '',
    ];
    $this->drupalPostForm('yamlform/test_element_extras', $edit, t('Submit'));
    $this->assertNoRaw('<li class="messages__item">Confirm Email field is required.</li>');

    /**************************************************************************/
    // code:yaml
    /**************************************************************************/

    // Check invalid YAML.
    $edit = [
      'yaml_basic' => "'not: valid",
    ];
    $this->drupalPostForm('yamlform/test_element_extras', $edit, t('Submit'));
    $this->assertRaw('<em class="placeholder">YAML basic</em> is not valid.');

    // Check valid YAML.
    $edit = [
      'yaml_basic' => 'is: valid',
    ];
    $this->drupalPostForm('yamlform/test_element_extras', $edit, t('Submit'));
    $this->assertNoRaw('<em class="placeholder">YAML basic</em> is not valid.');

    /**************************************************************************/
    // code:html
    /**************************************************************************/

    // Check invalid HTML.
    $edit = [
      'html_basic' => "<b>bold</bold>",
    ];
    $this->drupalPostForm('yamlform/test_element_extras', $edit, t('Submit'));
    $this->assertRaw('<em class="placeholder">HTML basic</em> is not valid.');
    $this->assertRaw('expected &#039;&gt;&#039;');

    // Check valid HTML.
    $edit = [
      'html_basic' => '<b>bold</b>',
    ];
    $this->drupalPostForm('yamlform/test_element_extras', $edit, t('Submit'));
    $this->assertNoRaw('<em class="placeholder">HTML basic</em> is not valid.');
    $this->assertNoRaw('expected &#039;&gt;&#039;');

    /**************************************************************************/
    // rating
    /**************************************************************************/

    $edit = [
      'rating_basic' => '4',
    ];
    $this->drupalPostForm('yamlform/test_element_extras', $edit, t('Submit'));
    $this->assertRaw("rating_basic: '4'");

    /**************************************************************************/
    // likert
    /**************************************************************************/

    // Check likert required.
    $this->drupalPostForm('yamlform/test_element_likert', [], t('Submit'));
    $this->assertRaw('Question 1 field is required.');
    $this->assertRaw('Question 2 field is required.');
    $this->assertRaw('Question 3 field is required.');

    // Check likert processing.
    $edit = [
      'likert[q1]' => '1',
      'likert[q2]' => '2',
      'likert[q3]' => '3',
    ];
    $this->drupalPostForm('yamlform/test_element_likert', $edit, t('Submit'));
    $this->assertRaw("likert:
  q1: '1'
  q2: '2'
  q3: '3'");

    /**************************************************************************/
    // markup
    /**************************************************************************/

    $this->drupalGet('yamlform/test_element_markup');
    $this->assertRaw('<p>This is normal markup</p>');
    $this->assertRaw('<p>This is only displayed on the form view.</p>');
    $this->assertNoRaw('<p>This is only displayed on the submission view.</p>');
    $this->assertRaw('<p>This is displayed on the both the form and submission view.</p>');

    $this->drupalPostForm('yamlform/test_element_markup', [], t('Preview'));
    $this->assertNoRaw('<p>This is normal markup</p>');
    $this->assertNoRaw('<p>This is only displayed on the form view.</p>');
    $this->assertRaw('<p>This is only displayed on the submission view.</p>');
    $this->assertRaw('<p>This is displayed on the both the form and submission view.</p>');

    /**************************************************************************/
    // contact (composite element)
    /**************************************************************************/

    // Check composite value.
    $this->drupalPostForm('yamlform/test_element_extras', [], t('Submit'));
    $this->assertRaw("contact_basic:
  name: 'John Smith'
  company: Acme
  email: example@example.com
  phone: 123-456-7890
  address: '100 Main Street'
  address_2: 'PO BOX 999'
  city: 'Hill Valley'
  state_province: 'New Jersey'
  postal_code: 11111-1111
  country: 'United States of America'");

    // Check validate required composite elements.
    $edit = [
      'contact_basic[name]' => '',
    ];
    $this->drupalPostForm('yamlform/test_element_extras', $edit, t('Submit'));
    $this->assertRaw('Name field is required.');

    /**************************************************************************/
    // creditcard (composite element)
    /**************************************************************************/

    // Check composite value.
    $this->drupalPostForm('yamlform/test_element_extras', [], t('Submit'));

    $this->assertRaw("creditcard:
  name: 'John Smith'
  type: VI
  number: '4111111111111111'
  civ: '111'
  expiration_month: '1'
  expiration_year: '2025'");
  }

}
