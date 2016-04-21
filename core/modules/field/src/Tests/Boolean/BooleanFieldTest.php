<?php

namespace Drupal\field\Tests\Boolean;

use Drupal\Component\Utility\Unicode;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\field\Entity\FieldConfig;
use Drupal\simpletest\WebTestBase;

/**
 * Tests boolean field functionality.
 *
 * @group field
 */
class BooleanFieldTest extends WebTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('entity_test', 'field_ui', 'options');

  /**
   * A field to use in this test class.
   *
   * @var \Drupal\field\Entity\FieldStorageConfig
   */
  protected $fieldStorage;

  /**
   * The field used in this test class.
   *
   * @var \Drupal\field\Entity\FieldConfig
   */
  protected $field;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->drupalLogin($this->drupalCreateUser(array(
      'view test entity',
      'administer entity_test content',
      'administer entity_test form display',
      'administer entity_test fields',
    )));
  }

  /**
   * Tests boolean field.
   */
  function testBooleanField() {
    $on = $this->randomMachineName();
    $off = $this->randomMachineName();
    $label = $this->randomMachineName();

    // Create a field with settings to validate.
    $field_name = Unicode::strtolower($this->randomMachineName());
    $this->fieldStorage = FieldStorageConfig::create(array(
      'field_name' => $field_name,
      'entity_type' => 'entity_test',
      'type' => 'boolean',
    ));
    $this->fieldStorage->save();
    $this->field = FieldConfig::create(array(
      'field_name' => $field_name,
      'entity_type' => 'entity_test',
      'bundle' => 'entity_test',
      'label' => $label,
      'required' => TRUE,
      'settings' => array(
        'on_label' => $on,
        'off_label' => $off,
      ),
    ));
    $this->field->save();

    // Create a form display for the default form mode.
    entity_get_form_display('entity_test', 'entity_test', 'default')
      ->setComponent($field_name, array(
        'type' => 'boolean_checkbox',
      ))
      ->save();
    // Create a display for the full view mode.
    entity_get_display('entity_test', 'entity_test', 'full')
      ->setComponent($field_name, array(
        'type' => 'boolean',
      ))
      ->save();

    // Display creation form.
    $this->drupalGet('entity_test/add');
    $this->assertFieldByName("{$field_name}[value]", '', 'Widget found.');
    $this->assertRaw($on);

    // Submit and ensure it is accepted.
    $edit = array(
      "{$field_name}[value]" => 1,
    );
    $this->drupalPostForm(NULL, $edit, t('Save'));
    preg_match('|entity_test/manage/(\d+)|', $this->url, $match);
    $id = $match[1];
    $this->assertText(t('entity_test @id has been created.', array('@id' => $id)));

    // Verify that boolean value is displayed.
    $entity = entity_load('entity_test', $id);
    $display = entity_get_display($entity->getEntityTypeId(), $entity->bundle(), 'full');
    $content = $display->build($entity);
    $this->setRawContent(\Drupal::service('renderer')->renderRoot($content));
    $this->assertRaw('<div class="field__item">' . $on . '</div>');

    // Test if we can change the on label.
    $on = $this->randomMachineName();
    $edit = array(
      'settings[on_label]' => $on,
    );
    $this->drupalPostForm('entity_test/structure/entity_test/fields/entity_test.entity_test.' . $field_name, $edit, t('Save settings'));
    // Check if we see the updated labels in the creation form.
    $this->drupalGet('entity_test/add');
    $this->assertRaw($on);

    // Test the display_label option.
    entity_get_form_display('entity_test', 'entity_test', 'default')
      ->setComponent($field_name, array(
        'type' => 'boolean_checkbox',
        'settings' => array(
          'display_label' => TRUE,
        )
      ))
      ->save();

    $this->drupalGet('entity_test/add');
    $this->assertFieldByName("{$field_name}[value]", '', 'Widget found.');
    $this->assertNoRaw($on);
    $this->assertText($this->field->label());

    // Go to the form display page and check if the default settings works as
    // expected.
    $fieldEditUrl = 'entity_test/structure/entity_test/form-display';
    $this->drupalGet($fieldEditUrl);

    // Click on the widget settings button to open the widget settings form.
    $this->drupalPostAjaxForm(NULL, array(), $field_name . "_settings_edit");

    $this->assertText(
      'Use field label instead of the "On label" as label',
      t('Display setting checkbox available.')
    );

    // Enable setting.
    $edit = array('fields[' . $field_name . '][settings_edit_form][settings][display_label]' => 1);
    $this->drupalPostAjaxForm(NULL, $edit, $field_name . "_plugin_settings_update");
    $this->drupalPostForm(NULL, NULL, 'Save');

    // Go again to the form display page and check if the setting
    // is stored and has the expected effect.
    $this->drupalGet($fieldEditUrl);
    $this->assertText('Use field label: Yes', 'Checking the display settings checkbox updated the value.');

    $this->drupalPostAjaxForm(NULL, array(), $field_name . "_settings_edit");
    $this->assertText(
      'Use field label instead of the "On label" as label',
      t('Display setting checkbox is available')
    );
    $this->assertFieldByXPath(
      '*//input[starts-with(@id, "edit-fields-' . $field_name . '-settings-edit-form-settings-display-label") and @value="1"]',
      TRUE,
      t('Display label changes label of the checkbox')
    );

    // Test the boolean field settings.
    $this->drupalGet('entity_test/structure/entity_test/fields/entity_test.entity_test.' . $field_name);
    $this->assertFieldById('edit-settings-on-label', $on);
    $this->assertFieldById('edit-settings-off-label', $off);
  }

}
