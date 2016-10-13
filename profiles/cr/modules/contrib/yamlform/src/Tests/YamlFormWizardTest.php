<?php

namespace Drupal\yamlform\Tests;

use Drupal\simpletest\WebTestBase;
use Drupal\yamlform\Entity\YamlForm;
use Drupal\Component\Serialization\Yaml;

/**
 * Tests for form wizard.
 *
 * @group YamlForm
 */
class YamlFormWizardTest extends WebTestBase {

  use YamlFormTestTrait;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['system', 'user', 'yamlform', 'yamlform_test'];

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
  }

  /**
   * {@inheritdoc}
   */
  public function tearDown() {
    $this->purgeSubmissions();
    parent::tearDown();
  }

  /**
   * Test form wizard.
   */
  public function testWizard() {
    $yamlform_wizard_advanced = YamlForm::load('test_form_wizard_advanced');

    // Get initial wizard start page (Your Information).
    $this->drupalGet('yamlform/test_form_wizard_advanced');
    // Check progress bar is set to 'Your Information'.
    $this->assertPattern('#<li class="yamlform-progress-bar__page yamlform-progress-bar__page--current">\s+<b>Your Information</b><span></span></li>#');
    // Check progress pages.
    $this->assertRaw('Page 1 of 5');
    // Check progress percentage.
    $this->assertRaw('(0%)');
    // Check draft button does not exist.
    $this->assertNoFieldById('edit-draft', 'Save Draft');
    // Check next button does exist.
    $this->assertFieldById('edit-next', 'Next Page >');
    // Check first name field does exist.
    $this->assertFieldById('edit-first-name', 'John');

    // Create a login user who can save drafts.
    $account = $this->drupalCreateUser();
    $this->drupalLogin($account);

    // Move to next page (Contact Information).
    $edit = [
      'first_name' => 'Jane',
    ];
    $this->drupalPostForm('yamlform/test_form_wizard_advanced', $edit, t('Next Page >'));
    // Check progress bar is set to 'Contact Information'.
    $this->assertPattern('#<li class="yamlform-progress-bar__page yamlform-progress-bar__page--done">\s+<b>Your Information</b><span></span></li>#');
    $this->assertPattern('#<li class="yamlform-progress-bar__page yamlform-progress-bar__page--current">\s+<b>Contact Information</b></li>#');
    // Check progress pages.
    $this->assertRaw('Page 2 of 5');
    // Check progress percentage.
    $this->assertRaw('(25%)');

    // Check draft button does exist.
    $this->assertFieldById('edit-draft', 'Save Draft');
    // Check previous button does exist.
    $this->assertFieldById('edit-previous', '< Previous Page');
    // Check next button does exist.
    $this->assertFieldById('edit-next', 'Next Page >');
    // Check email field does exist.
    $this->assertFieldById('edit-email', 'johnsmith@example.com');

    // Move to previous page (Your Information) while posting data new data
    // via autosave.
    $edit = [
      'email' => 'janesmith@example.com',
    ];
    $this->drupalPostForm(NULL, $edit, t('< Previous Page'));
    // Check progress bar is set to 'Your Information'.
    $this->assertPattern('#<li class="yamlform-progress-bar__page yamlform-progress-bar__page--current">\s+<b>Your Information</b><span></span></li>#');
    // Check progress pages.
    $this->assertRaw('Page 1 of 5');
    // Check progress percentage.
    $this->assertRaw('(0%)');

    // Check first name set to Jane.
    $this->assertFieldById('edit-first-name', 'Jane');
    // Check gender is still set to Male.
    $this->assertFieldChecked('edit-gender-male');

    // Change gender from Male to Female.
    $edit = [
      'gender' => 'Female',
    ];
    $this->drupalPostForm(NULL, $edit, t('Save Draft'));
    // Check first name set to Jane.
    $this->assertFieldById('edit-first-name', 'Jane');
    // Check gender is now set to Female.
    $this->assertFieldChecked('edit-gender-female');

    // Move to next page (Contact Information).
    $this->drupalPostForm('yamlform/test_form_wizard_advanced', [], t('Next Page >'));
    // Check progress bar is set to 'Contact Information'.
    $this->assertPattern('#<li class="yamlform-progress-bar__page yamlform-progress-bar__page--current">\s+<b>Contact Information</b></li>#');
    // Check progress pages.
    $this->assertRaw('Page 2 of 5');
    // Check progress percentage.
    $this->assertRaw('(25%)');

    // Check email field is now janesmith@example.com.
    $this->assertFieldById('edit-email', 'janesmith@example.com');

    // Save draft which saves the 'current_page'.
    $edit = [
      'phone' => '111-111-1111',
    ];
    $this->drupalPostForm(NULL, $edit, t('Save Draft'));
    // Complete reload the form.
    $this->drupalGet('yamlform/test_form_wizard_advanced');
    // Check progress bar is still set to 'Contact Information'.
    $this->assertPattern('#<li class="yamlform-progress-bar__page yamlform-progress-bar__page--current">\s+<b>Contact Information</b></li>#');

    // Move to last page (Your Feedback).
    $this->drupalPostForm(NULL, [], t('Next Page >'));
    // Check progress bar is set to 'Your Feedback'.
    $this->assertPattern('#<li class="yamlform-progress-bar__page yamlform-progress-bar__page--current">\s+<b>Your Feedback</b></li>#');
    // Check previous button does exist.
    $this->assertFieldById('edit-previous', '< Previous Page');
    // Check next button is labeled 'Preview'.
    $this->assertFieldById('edit-next', 'Preview');
    // Check submit button does exist.
    $this->assertFieldById('edit-submit', 'Submit');

    // Move to preview.
    $edit = [
      'comments' => 'This is working fine.',
    ];
    $this->drupalPostForm(NULL, $edit, t('Preview'));
    // Check progress bar is set to 'Preview'.
    $this->assertPattern('#<li class="yamlform-progress-bar__page yamlform-progress-bar__page--current">\s+<b>Preview</b></li>#');
    // Check progress pages.
    $this->assertRaw('Page 4 of 5');
    // Check progress percentage.
    $this->assertRaw('(75%)');

    // Check preview values.
    $this->assertRaw('<b>Last Name</b><br/>Smith<br/><br/>');
    $this->assertRaw('<b>Gender</b><br/>Female<br/><br/>');
    $this->assertRaw('<b>Email</b><br/><a href="mailto:janesmith@example.com">janesmith@example.com</a><br/><br/>');
    $this->assertRaw('<b>Phone</b><br/><a href="tel:111-111-1111">111-111-1111</a><br/><br/>');
    $this->assertRaw('This is working fine.<br/><br/>');

    // Submit the form.
    $this->drupalPostForm(NULL, [], t('Submit'));
    // Check progress bar is set to 'Completed'.
    $this->assertPattern('#<li class="yamlform-progress-bar__page yamlform-progress-bar__page--current">\s+<b>Complete</b><span></span></li>#');
    // Check progress pages.
    $this->assertRaw('Page 5 of 5');
    // Check progress percentage.
    $this->assertRaw('(100%)');

    /* Custom wizard settings */
    $this->drupalLogout();

    // Check global next and previous button labels.
    \Drupal::configFactory()->getEditable('yamlform.settings')
      ->set('settings.default_wizard_next_button_label', '{global wizard next}')
      ->set('settings.default_wizard_prev_button_label', '{global wizard previous}')
      ->save();
    $this->drupalPostForm('yamlform/test_form_wizard_advanced', [], t('{global wizard next}'));

    // Check progress bar.
    $this->assertRaw('class="yamlform-progress-bar"');
    // Check previous button.
    $this->assertFieldById('edit-previous', '{global wizard previous}');
    // Check next button.
    $this->assertFieldById('edit-next', '{global wizard next}');

    // Check form next and previous button labels.
    $yamlform_wizard_advanced->setSettings([
      'wizard_next_button_label' => '{yamlform wizard next}',
      'wizard_prev_button_label' => '{yamlform wizard previous}',
      'preview_next_button_label' => '{yamlform preview next}',
      'preview_prev_button_label' => '{yamlform preview previous}',
    ]);
    $yamlform_wizard_advanced->save();
    $this->drupalPostForm('yamlform/test_form_wizard_advanced', [], t('{yamlform wizard next}'));
    // Check previous button.
    $this->assertFieldById('edit-previous', '{yamlform wizard previous}');
    // Check next button.
    $this->assertFieldById('edit-next', '{yamlform wizard next}');

    // Check custom next and previous button labels.
    $elements = Yaml::decode($yamlform_wizard_advanced->get('elements'));
    $elements['contact']['#next_button_label'] = '{elements wizard next}';
    $elements['contact']['#prev_button_label'] = '{elements wizard previous}';
    $yamlform_wizard_advanced->set('elements', Yaml::encode($elements));
    $yamlform_wizard_advanced->save();
    $this->drupalPostForm('yamlform/test_form_wizard_advanced', [], t('{yamlform wizard next}'));

    // Check previous button.
    $this->assertFieldById('edit-previous', '{elements wizard previous}');
    // Check next button.
    $this->assertFieldById('edit-next', '{elements wizard next}');

    // Check form next and previous button labels.
    $yamlform_wizard_advanced->setSettings([
      'wizard_progress_bar' => FALSE,
      'wizard_progress_pages' => TRUE,
      'wizard_progress_percentage' => TRUE,
    ] + $yamlform_wizard_advanced->getSettings());
    $yamlform_wizard_advanced->save();
    $this->drupalGet('yamlform/test_form_wizard_advanced');

    // Check no progress bar.
    $this->assertNoRaw('class="yamlform-progress-bar"');
    // Check progress pages.
    $this->assertRaw('Page 1 of 4');
    // Check progress percentage.
    $this->assertRaw('(0%)');

    // Check global complete labels.
    $yamlform_wizard_advanced->setSettings([
      'wizard_progress_bar' => TRUE,
    ] + $yamlform_wizard_advanced->getSettings());
    $yamlform_wizard_advanced->save();
    \Drupal::configFactory()->getEditable('yamlform.settings')
      ->set('settings.default_wizard_complete_label', '{global complete}')
      ->save();
    $this->drupalGet('yamlform/test_form_wizard_advanced');
    $this->assertRaw('{global complete}');

    // Check form complete label.
    $yamlform_wizard_advanced->setSettings([
      'wizard_progress_bar' => TRUE,
      'wizard_complete_label' => '{yamlform complete}',
    ] + $yamlform_wizard_advanced->getSettings());
    $yamlform_wizard_advanced->save();
    $this->drupalGet('yamlform/test_form_wizard_advanced');
    $this->assertRaw('{yamlform complete}');

    // Check form exclude complete.
    $yamlform_wizard_advanced->setSettings([
      'wizard_complete' => FALSE,
    ] + $yamlform_wizard_advanced->getSettings());
    $yamlform_wizard_advanced->save();
    $this->drupalGet('yamlform/test_form_wizard_advanced');

    // Check complete label.
    $this->assertRaw('class="yamlform-progress-bar"');
    // Check complete is missing from confirmation page.
    $this->assertNoRaw('{yamlform complete}');
    $this->drupalGet('yamlform/test_form_wizard_advanced/confirmation');
    $this->assertNoRaw('class="yamlform-progress-bar"');
  }

}
