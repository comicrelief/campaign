<?php

namespace Drupal\yamlform\Tests;

use Drupal\yamlform\Entity\YamlForm;

/**
 * Tests for form submission form.
 *
 * @group YamlForm
 */
class YamlFormSubmissionFormTest extends YamlFormTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['system', 'user', 'yamlform', 'yamlform_test'];

  /**
   * Tests prepare elements.
   */
  public function testForm() {
    /* Test form#validate form handling */
    $yamlform_validate = YamlForm::load('test_form_validate');
    $this->postSubmission($yamlform_validate, [], t('Submit'));
    $this->assertRaw('Custom element is required.');

    $this->postSubmission($yamlform_validate, ['custom' => 'value'], t('Submit'));
    $this->assertNoRaw('Custom element is required.');
  }

}
