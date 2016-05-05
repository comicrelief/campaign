<?php

/**
 * @file
 * Definition of Drupal\yamlform\test\YamlFormTranslationTest.
 */

namespace Drupal\yamlform\Tests;

use Drupal\simpletest\WebTestBase;
use Drupal\Component\Serialization\Yaml;

/**
 * Tests for YAML form translation.
 *
 * @group YamlForm
 */
class YamlFormTranslationTest extends WebTestBase {

  use YamlFormTestTrait;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['system', 'user', 'block', 'yamlform', 'yamlform_translation_test'];

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->placeBlocks();

    $admin_user = $this->drupalCreateUser(['access content', 'administer yamlform', 'administer yamlform submission', 'translate configuration']);
    $this->drupalLogin($admin_user);
  }

  /**
   * Tests YAML form translate.
   */
  public function testTranslate() {
    $inputs_raw = \Drupal::config('yamlform.yamlform.contact')->get('inputs');
    $inputs = Yaml::decode($inputs_raw);

    // Check 'Contact' translate tab.
    $this->drupalGet('admin/structure/yamlform/manage/contact');
    $this->assertRaw('>Translate<');

    // Check 'Contact' translations.
    $this->drupalGet('admin/structure/yamlform/manage/contact/translate');
    $this->assertRaw('<a href="' . base_path() . 'admin/structure/yamlform/manage/contact/translate/es/edit">Edit</a>');

    // Check 'Contact' Spanish translations.
    $this->drupalGet('admin/structure/yamlform/manage/contact/translate/es/edit');
    $this->assertFieldByName('translation[config_names][yamlform.yamlform.contact][title]', 'Contacto');
    $this->assertField('translation[config_names][yamlform.yamlform.contact][inputs]');

    // Check translation validation.
    $edit = [
      'translation[config_names][yamlform.yamlform.contact][inputs]' => Yaml::encode($inputs),
    ];
    $this->drupalPostForm('admin/structure/yamlform/manage/contact/translate/es/edit', $edit, t('Save translation'));

    // Check remove an element validation.
    $test_inputs = $inputs;
    unset($test_inputs['name']);
    $edit = [
      'translation[config_names][yamlform.yamlform.contact][inputs]' => Yaml::encode($test_inputs),
    ];
    $this->drupalPostForm('admin/structure/yamlform/manage/contact/translate/es/edit', $edit, t('Save translation'));
    $this->assertRaw('<li>The <em class="placeholder">name</em> element can not be removed.</li>');

    // Check add an element validation.
    $test_inputs = $inputs;
    $test_inputs['name_altered'] = $test_inputs['name'];
    $edit = [
      'translation[config_names][yamlform.yamlform.contact][inputs]' => Yaml::encode($test_inputs),
    ];
    $this->drupalPostForm('admin/structure/yamlform/manage/contact/translate/es/edit', $edit, t('Save translation'));
    $this->assertRaw('<li>The <em class="placeholder">name_altered</em> element can not be added.</li>');

    // Check remove a property validation.
    $test_inputs = $inputs;
    unset($test_inputs['name']['#title']);
    $edit = [
      'translation[config_names][yamlform.yamlform.contact][inputs]' => Yaml::encode($test_inputs),
    ];
    $this->drupalPostForm('admin/structure/yamlform/manage/contact/translate/es/edit', $edit, t('Save translation'));
    $this->assertRaw('<li>The <em class="placeholder">name.#title</em> property can not be removed.</li>');

    // Check add a property validation.
    $test_inputs = $inputs;
    $test_inputs['name']['#new'] = TRUE;
    $edit = [
      'translation[config_names][yamlform.yamlform.contact][inputs]' => Yaml::encode($test_inputs),
    ];
    $this->drupalPostForm('admin/structure/yamlform/manage/contact/translate/es/edit', $edit, t('Save translation'));
    $this->assertRaw('<li>The <em class="placeholder">name.#new</em> property can not be added.</li>');

    // Check translated YAML form options.
    $this->drupalGet('es/yamlform/example_options');
    $this->assertRaw('<label for="edit-yes-no-yes" class="option">Sí</label>');
    $this->assertRaw('<option value="840">Estados Unidos de América</option>');
  }

}
