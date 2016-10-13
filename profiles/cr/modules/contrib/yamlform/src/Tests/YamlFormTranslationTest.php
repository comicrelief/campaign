<?php

namespace Drupal\yamlform\Tests;

use Drupal\simpletest\WebTestBase;
use Drupal\Component\Serialization\Yaml;

/**
 * Tests for form translation.
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
  public static $modules = ['system', 'user', 'block', 'yamlform', 'yamlform_examples', 'yamlform_translation_test'];

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
   * Tests form translate.
   */
  public function testTranslate() {
    $elements_raw = \Drupal::config('yamlform.yamlform.contact')->get('elements');
    $elements = Yaml::decode($elements_raw);

    // Check 'Contact' translate tab.
    $this->drupalGet('admin/structure/yamlform/manage/contact');
    $this->assertRaw('>Translate<');

    // Check 'Contact' translations.
    $this->drupalGet('admin/structure/yamlform/manage/contact/translate');
    $this->assertRaw('<a href="' . base_path() . 'admin/structure/yamlform/manage/contact/translate/es/edit">Edit</a>');

    // Check 'Contact' Spanish translations.
    $this->drupalGet('admin/structure/yamlform/manage/contact/translate/es/edit');
    $this->assertFieldByName('translation[config_names][yamlform.yamlform.contact][title]', 'Contacto');
    $this->assertField('translation[config_names][yamlform.yamlform.contact][elements]');

    // Check translation validation.
    $edit = [
      'translation[config_names][yamlform.yamlform.contact][elements]' => Yaml::encode($elements),
    ];
    $this->drupalPostForm('admin/structure/yamlform/manage/contact/translate/es/edit', $edit, t('Save translation'));

    // Check remove an element validation.
    $test_element = $elements;
    unset($test_element['name']);
    $edit = [
      'translation[config_names][yamlform.yamlform.contact][elements]' => Yaml::encode($test_element),
    ];
    $this->drupalPostForm('admin/structure/yamlform/manage/contact/translate/es/edit', $edit, t('Save translation'));
    $this->assertRaw('<li>The <em class="placeholder">name</em> element can not be removed.</li>');

    // Check add an element validation.
    $test_element = $elements;
    $test_element['name_altered'] = $test_element['name'];
    $edit = [
      'translation[config_names][yamlform.yamlform.contact][elements]' => Yaml::encode($test_element),
    ];
    $this->drupalPostForm('admin/structure/yamlform/manage/contact/translate/es/edit', $edit, t('Save translation'));
    $this->assertRaw('<li>The <em class="placeholder">name_altered</em> element can not be added.</li>');

    // Check remove a property validation.
    $test_element = $elements;
    unset($test_element['name']['#title']);
    $edit = [
      'translation[config_names][yamlform.yamlform.contact][elements]' => Yaml::encode($test_element),
    ];
    $this->drupalPostForm('admin/structure/yamlform/manage/contact/translate/es/edit', $edit, t('Save translation'));
    $this->assertRaw('<li>The <em class="placeholder">name.#title</em> property can not be removed.</li>');

    // Check add a property validation.
    $test_element = $elements;
    $test_element['name']['#new'] = TRUE;
    $edit = [
      'translation[config_names][yamlform.yamlform.contact][elements]' => Yaml::encode($test_element),
    ];
    $this->drupalPostForm('admin/structure/yamlform/manage/contact/translate/es/edit', $edit, t('Save translation'));
    $this->assertRaw('<li>The <em class="placeholder">name.#new</em> property can not be added.</li>');

    // Check translation warning.
    $this->drupalGet('admin/structure/yamlform/manage/contact');
    $this->assertText('The Contact form has translations and its elements and properties can not be changed.');

    // Check translated form options.
    $this->drupalGet('es/yamlform/example_options');
    $this->assertRaw('<option value="Yes">Sí</option>');
    $this->assertRaw('<option value="840">Estados Unidos de América</option>');
  }

}
