<?php

namespace Drupal\yamlform\Tests;

use Drupal\simpletest\WebTestBase;
use Drupal\yamlform\Entity\YamlForm;

/**
 * Tests for form element other.
 *
 * @group YamlForm
 */
class YamlFormElementOtherTest extends WebTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['yamlform', 'yamlform_test'];

  /**
   * Tests building of other elements.
   */
  public function testBuildingOtherElements() {
    $yamlform = YamlForm::load('test_element_other');

    $this->drupalGet('yamlform/test_element_other');

    /**************************************************************************/
    // select_other
    /**************************************************************************/

    // Check basic select_other.
    $this->assertRaw('<select data-drupal-selector="edit-select-other-basic-select" id="edit-select-other-basic-select" name="select_other_basic[select]" class="form-select">');
    $this->assertRaw('<input data-drupal-selector="edit-select-other-basic-other" type="text" id="edit-select-other-basic-other" name="select_other_basic[other]" value="Four" size="60" maxlength="128" placeholder="Enter other..." class="form-text" />');
    $this->assertRaw('<option value="_other_" selected="selected">Other...</option>');

    // Check advanced select_other w/ custom label.
    $this->assertRaw('<label for="edit-select-other-advanced" class="js-form-required form-required">Select other advanced</label>');
    $this->assertRaw('<select data-drupal-selector="edit-select-other-advanced-select" id="edit-select-other-advanced-select" name="select_other_advanced[select]" class="form-select required" required="required" aria-required="true">');
    $this->assertRaw('<option value="_other_" selected="selected">Is there another option you wish to enter?</option>');
    $this->assertRaw('<label for="edit-select-other-advanced-other">Other</label>');
    $this->assertRaw('<input data-drupal-selector="edit-select-other-advanced-other" aria-describedby="edit-select-other-advanced-other--description" type="text" id="edit-select-other-advanced-other" name="select_other_advanced[other]" value="Four" size="20" maxlength="20" placeholder="What is this other option" class="form-text" />');
    $this->assertRaw('<div id="edit-select-other-advanced-other--description" class="description">');
    $this->assertRaw('Other select description');

    // Check multiple select_other.
    $this->assertRaw('<label for="edit-select-other-multiple">Select other multiple</label>');
    $this->assertRaw('<select data-drupal-selector="edit-select-other-multiple-select" multiple="multiple" name="select_other_multiple[select][]" id="edit-select-other-multiple-select" class="form-select">');
    $this->assertRaw('<input data-drupal-selector="edit-select-other-multiple-other" type="text" id="edit-select-other-multiple-other" name="select_other_multiple[other]" value="Four" size="60" maxlength="128" placeholder="Enter other..." class="form-text" />');

    /**************************************************************************/
    // checkboxes_other
    /**************************************************************************/

    // Check basic checkboxes.
    $this->assertRaw('<label for="edit-checkboxes-other-basic">Checkboxes other basic</label>');
    $this->assertRaw('<input data-drupal-selector="edit-checkboxes-other-basic-checkboxes-other-" type="checkbox" id="edit-checkboxes-other-basic-checkboxes-other-" name="checkboxes_other_basic[checkboxes][_other_]" value="_other_" checked="checked" class="form-checkbox" />');
    $this->assertRaw('<label for="edit-checkboxes-other-basic-checkboxes-other-" class="option">Other...</label>');
    $this->assertRaw('<input data-drupal-selector="edit-checkboxes-other-basic-other" type="text" id="edit-checkboxes-other-basic-other" name="checkboxes_other_basic[other]" value="Four" size="60" maxlength="128" placeholder="Enter other..." class="form-text" />');

    // Check advanced checkboxes.
    $this->assertRaw('<label for="edit-checkboxes-other-advanced" class="js-form-required form-required">Checkboxes other advanced</label>');
    $this->assertRaw('<input data-drupal-selector="edit-checkboxes-other-advanced-other" aria-describedby="edit-checkboxes-other-advanced-other--description" type="text" id="edit-checkboxes-other-advanced-other" name="checkboxes_other_advanced[other]" value="Four" size="60" maxlength="128" placeholder="What is this other option" class="form-text" />');
    $this->assertRaw('<div id="edit-checkboxes-other-advanced-other--description" class="description">');
    $this->assertRaw('Other checkbox description');

    /**************************************************************************/
    // radios_other
    /**************************************************************************/

    // Check basic radios_other.
    $this->assertRaw('<label for="edit-radios-other-basic">Radios other basic</label>');
    $this->assertRaw('<input data-drupal-selector="edit-radios-other-basic-radios-other-" type="radio" id="edit-radios-other-basic-radios-other-" name="radios_other_basic[radios]" value="_other_" checked="checked" class="form-radio" />');
    $this->assertRaw('<label for="edit-radios-other-basic-radios-other-" class="option">Other...</label>');
    $this->assertRaw('<input data-drupal-selector="edit-radios-other-basic-other" type="text" id="edit-radios-other-basic-other" name="radios_other_basic[other]" value="Four" size="60" maxlength="128" placeholder="Enter other..." class="form-text" />');

    // Check advanced radios_other w/ custom label.
    $this->assertRaw('<label for="edit-radios-other-advanced" class="js-form-required form-required">Radios other advanced</label>');
    $this->assertRaw('<input data-drupal-selector="edit-radios-other-advanced-radios-other-" type="radio" id="edit-radios-other-advanced-radios-other-" name="radios_other_advanced[radios]" value="_other_" checked="checked" class="form-radio" />');
    $this->assertRaw('<input data-drupal-selector="edit-radios-other-advanced-other" aria-describedby="edit-radios-other-advanced-other--description" type="text" id="edit-radios-other-advanced-other" name="radios_other_advanced[other]" value="Four" size="60" maxlength="128" placeholder="What is this other option" class="form-text" />');
    $this->assertRaw('<div id="edit-radios-other-advanced-other--description" class="description">');
    $this->assertRaw('Other radio description');
  }

  /**
   * Tests value processing for other elements.
   */
  public function testProcessingOtherElements() {
    $yamlform = YamlForm::load('test_element_other');

    /**************************************************************************/
    // select_other
    /**************************************************************************/

    // Check select other is required when selected.
    $edit = [
      'select_other_basic[select]' => '_other_',
      'select_other_basic[other]' => '',
    ];
    $this->drupalPostForm('yamlform/test_element_other', $edit, t('Submit'));
    $this->assertRaw('Select other basic field is required.');

    // Check select other is not required when not selected.
    $edit = [
      'select_other_basic[select]' => '',
      'select_other_basic[other]' => '',
    ];
    $this->drupalPostForm('yamlform/test_element_other', $edit, t('Submit'));
    $this->assertNoRaw('Select other basic field is required.');

    // Check select other required validation.
    $edit = [
      'select_other_advanced[select]' => '',
      'select_other_advanced[other]' => '',
    ];
    $this->drupalPostForm('yamlform/test_element_other', $edit, t('Submit'));
    $this->assertRaw('Select other advanced field is required.');

    // Check select other processing w/ other.
    $edit = [
      'select_other_advanced[select]' => '_other_',
      'select_other_advanced[other]' => 'Five',
    ];
    $this->drupalPostForm('yamlform/test_element_other', $edit, t('Submit'));
    $this->assertRaw('select_other_advanced: Five');

    // Check select other processing w/o other.
    $edit = [
      'select_other_advanced[select]' => 'One',
      // This value is ignored, because 'select_other_advanced[select]' is not set to '_other'.
      'select_other_advanced[other]' => 'Five',
    ];
    $this->drupalPostForm('yamlform/test_element_other', $edit, t('Submit'));
    $this->assertRaw('select_other_advanced: One');
    $this->assertNoRaw('select_other_advanced: Five');

    // Check select other validation is required when default value is NULL.
    $elements = $yamlform->getElementsDecoded();
    $elements['select_other']['select_other_advanced']['#default_value'] = NULL;
    $yamlform->setElements($elements)->save();
    $this->drupalPostForm('yamlform/test_element_other', [], t('Submit'));
    $this->assertRaw('Select other advanced field is required.');

    // Check select other validation is skipped when #access is set to FALSE.
    $elements['select_other']['select_other_advanced']['#access'] = FALSE;
    $yamlform->setElements($elements)->save();
    $this->drupalPostForm('yamlform/test_element_other', [], t('Submit'));
    $this->assertNoRaw('Select other advanced field is required.');

    /**************************************************************************/
    // radios_other
    /**************************************************************************/

    // Check radios other required when checked.
    $edit = [
      'radios_other_basic[radios]' => '_other_',
      'radios_other_basic[other]' => '',
    ];
    $this->drupalPostForm('yamlform/test_element_other', $edit, t('Submit'));
    $this->assertRaw('Radios other basic field is required.');

    // Check radios other not required when not checked.
    $edit = [
      'radios_other_basic[radios]' => 'One',
      'radios_other_basic[other]' => '',
    ];
    $this->drupalPostForm('yamlform/test_element_other', $edit, t('Submit'));
    $this->assertNoRaw('Radios other basic field is required.');

    // Check radios other required validation.
    $edit = [
      'radios_other_advanced[radios]' => '_other_',
      'radios_other_advanced[other]' => '',
    ];
    $this->drupalPostForm('yamlform/test_element_other', $edit, t('Submit'));
    $this->assertRaw('Radios other advanced field is required.');

    // Check radios other processing w/ other.
    $edit = [
      'radios_other_advanced[radios]' => '_other_',
      'radios_other_advanced[other]' => 'Five',
    ];
    $this->drupalPostForm('yamlform/test_element_other', $edit, t('Submit'));
    $this->assertRaw('radios_other_advanced: Five');

    // Check radios other processing w/o other.
    $edit = [
      'radios_other_advanced[radios]' => 'One',
      // This value is ignored, because 'radios_other_advanced[radios]' is not set to '_other'.
      'radios_other_advanced[other]' => 'Five',
    ];
    $this->drupalPostForm('yamlform/test_element_other', $edit, t('Submit'));
    $this->assertRaw('radios_other_advanced: One');
    $this->assertNoRaw('radios_other_advanced: Five');

    /**************************************************************************/
    // checkboxes_other
    /**************************************************************************/

    // Check checkboxes other required when checked.
    $edit = [
      'checkboxes_other_basic[checkboxes][_other_]' => TRUE,
      'checkboxes_other_basic[other]' => '',
    ];
    $this->drupalPostForm('yamlform/test_element_other', $edit, t('Submit'));
    $this->assertRaw('Checkboxes other basic field is required.');

    // Check checkboxes other not required when not checked.
    $edit = [
      'checkboxes_other_basic[checkboxes][_other_]' => FALSE,
      'checkboxes_other_basic[other]' => '',
    ];
    $this->drupalPostForm('yamlform/test_element_other', $edit, t('Submit'));
    $this->assertNoRaw('Checkboxes other basic field is required.');

    // Check checkboxes other required validation.
    $edit = [
      'checkboxes_other_advanced[checkboxes][One]' => FALSE,
      'checkboxes_other_advanced[checkboxes][Two]' => FALSE,
      'checkboxes_other_advanced[checkboxes][Three]' => FALSE,
      'checkboxes_other_advanced[checkboxes][_other_]' => TRUE,
      'checkboxes_other_advanced[other]' => '',
    ];
    $this->drupalPostForm('yamlform/test_element_other', $edit, t('Submit'));
    $this->assertRaw('Checkboxes other advanced field is required.');

    // Check checkboxes other processing w/ other.
    $edit = [
      'checkboxes_other_advanced[checkboxes][One]' => FALSE,
      'checkboxes_other_advanced[checkboxes][Two]' => FALSE,
      'checkboxes_other_advanced[checkboxes][Three]' => FALSE,
      'checkboxes_other_advanced[checkboxes][_other_]' => TRUE,
      'checkboxes_other_advanced[other]' => 'Five',
    ];
    $this->drupalPostForm('yamlform/test_element_other', $edit, t('Submit'));
    $this->assertRaw('checkboxes_other_advanced:
  - Five');

    // Check checkboxes other processing w/o other.
    $edit = [
      'checkboxes_other_advanced[checkboxes][One]' => TRUE,
      'checkboxes_other_advanced[checkboxes][Two]' => FALSE,
      'checkboxes_other_advanced[checkboxes][Three]' => FALSE,
      'checkboxes_other_advanced[checkboxes][_other_]' => FALSE,
      // This value is ignored, because 'radios_other_advanced[radios]' is not set to '_other'.
      'checkboxes_other_advanced[other]' => 'Five',
    ];
    $this->drupalPostForm('yamlform/test_element_other', $edit, t('Submit'));
    $this->assertRaw('checkboxes_other_advanced:
  - One');
    $this->assertNoRaw('checkboxes_other_advanced:
  - Five');
  }

}
