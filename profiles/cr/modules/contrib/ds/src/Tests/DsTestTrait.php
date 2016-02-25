<?php

/**
 * @file
 * Contains \Drupal\ds\Tests\DsTestTrait.
 */

namespace Drupal\ds\Tests;

/**
 * Provides common functionality for the Display Suite test classes.
 */
trait DsTestTrait {

  /**
   * Select a layout.
   */
  function dsSelectLayout($edit = array(), $assert = array(), $url = 'admin/structure/types/manage/article/display', $options = array()) {
    $edit += array(
      'layout' => 'ds_2col_stacked',
    );

    $this->drupalPostForm($url, $edit, t('Save'), $options);

    $assert += array(
      'regions' => array(
        'header' => '<td colspan="8">' . t('Header') . '</td>',
        'left' => '<td colspan="8">' . t('Left') . '</td>',
        'right' => '<td colspan="8">' . t('Right') . '</td>',
        'footer' => '<td colspan="8">' . t('Footer') . '</td>',
      ),
    );

    foreach ($assert['regions'] as $region => $raw) {
      $this->assertRaw($region, t('Region @region found', array('@region' => $region)));
    }
  }

  /**
   * Configure classes
   */
  function dsConfigureClasses($edit = array()) {

    $edit += array(
      'regions' => "class_name_1\nclass_name_2|Friendly name"
    );

    $this->drupalPostForm('admin/structure/ds/classes', $edit, t('Save configuration'));
    $this->assertText(t('The configuration options have been saved.'), t('CSS classes configuration saved'));
    $this->assertRaw('class_name_1', 'Class name 1 found');
    $this->assertRaw('class_name_2', 'Class name 1 found');
  }

  /**
   * Configure classes on a layout.
   */
  function dsSelectClasses($edit = array(), $url = 'admin/structure/types/manage/article/display') {

    $edit += array(
      "layout_configuration[ds_classes][header][]" => 'class_name_1',
      "layout_configuration[ds_classes][footer][]" => 'class_name_2',
    );

    $this->drupalPostForm($url, $edit, t('Save'));
  }

  /**
   * Configure Field UI.
   */
  function dsConfigureUI($edit, $url = 'admin/structure/types/manage/article/display') {
    $this->drupalPostForm($url, $edit, t('Save'));
  }

  /**
   * Edit field formatter settings.
   */
  function dsEditFormatterSettings($edit, $field_name = 'body', $url = 'admin/structure/types/manage/article/display') {
    $element_value = 'edit ' . $field_name;
    $this->drupalPostForm($url, array(), $element_value);

    if (isset($edit['fields[' . $field_name . '][settings_edit_form][third_party_settings][ds][ft][id]'])) {
      $this->drupalPostForm(NULL, array('fields[' . $field_name . '][settings_edit_form][third_party_settings][ds][ft][id]' => $edit['fields[' . $field_name . '][settings_edit_form][third_party_settings][ds][ft][id]']), t('Update'));
      $this->drupalPostForm(NULL, array(), $element_value);
      unset($edit['fields[' . $field_name . '][settings_edit_form][third_party_settings][ds][ft][id]']);
    }

    $this->drupalPostForm(NULL, $edit, t('Update'));
    $this->drupalPostForm(NULL, array(), t('Save'));
  }

  /**
   * Edit limit.
   */
  function dsEditLimitSettings($edit, $field_name = 'body', $url = 'admin/structure/types/manage/article/display') {
    $element_value = 'edit ' . $field_name;
    $this->drupalPostForm($url, array(), $element_value);

    if (isset($edit['fields[' . $field_name . '][settings_edit_form][third_party_settings][ds][ft][id]'])) {
      $this->drupalPostForm(NULL, array('fields[' . $field_name . '][settings_edit_form][third_party_settings][ds][ds_limit]' => $edit['fields[' . $field_name . '][settings_edit_form][third_party_settings][ds][ds_limit]']), t('Update'));
      $this->drupalPostForm(NULL, array(), $element_value);
      unset($edit['fields[' . $field_name . '][settings_edit_form][third_party_settings][ds][ds_limit]']);
    }

    $this->drupalPostForm(NULL, $edit, t('Update'));
    $this->drupalPostForm(NULL, array(), t('Save'));
  }

  /**
   * Create a token field.
   *
   * @param array $edit
   *   An optional array of field properties.
   * @param string $url
   *   The url to post to.
   */
  function dsCreateTokenField($edit = array(), $url = 'admin/structure/ds/fields/manage_token') {
    $edit += array(
      'name' => 'Test field',
      'id' => 'test_field',
      'entities[node]' => '1',
      'content[value]' => 'Test field',
    );

    $this->drupalPostForm($url, $edit, t('Save'));
    $this->assertText(t('The field ' . $edit['name'] . ' has been saved'), t('@name field has been saved', array('@name' => $edit['name'])));
  }

  /**
   * Create a block field.
   *
   * @param $edit
   *   An optional array of field properties.
   */
  function dsCreateBlockField($edit = array(), $url = 'admin/structure/ds/fields/manage_block') {
    $edit += array(
      'name' => 'Test block field',
      'id' => 'test_block_field',
      'entities[node]' => '1',
      'block' => 'system_powered_by_block',
    );

    $this->drupalPostForm($url, $edit, t('Save'));
    $this->assertText(t('The field ' . $edit['name'] . ' has been saved'), t('@name field has been saved', array('@name' => $edit['name'])));
  }

  /**
   * Utility function to setup for all kinds of tests.
   *
   * @param $label
   *   How the body label must be set.
   */
  function entitiesTestSetup($label = 'above') {

    // Create a node.
    $settings = array('type' => 'article', 'promote' => 1);
    $node = $this->drupalCreateNode($settings);

    // Create field CSS classes.
    $edit = array('fields' => "test_field_class\ntest_field_class_2|Field class 2\n[node:nid]");
    $this->drupalPostForm('admin/structure/ds/classes', $edit, t('Save configuration'));

    // Create a token field.
    $token_field = array(
      'name' => 'Token field',
      'id' => 'token_field',
      'entities[node]' => '1',
      'content[value]' => '[node:title]',
    );
    $this->dsCreateTokenField($token_field);

    // Select layout.
    $this->dsSelectLayout();

    // Configure fields.
    $fields = array(
      'fields[dynamic_token_field:node-token_field][region]' => 'header',
      'fields[body][region]' => 'right',
      'fields[node_link][region]' => 'footer',
      'fields[body][label]' => $label,
      'fields[node_submitted_by][region]' => 'header',
    );
    $this->dsConfigureUI($fields);

    return $node;
  }

  /**
   * Utility function to clear field settings.
   */
  function entitiesClearFieldSettings() {
    $display = entity_get_display('node', 'article', 'default');

    // Remove all third party settings from components.
    foreach ($display->getComponents() as $key => $info) {
      $info['third_party_settings'] = array();
      $display->setComponent($key, $info);
    }

    // Remove entity display third party settings.
    $tps = $display->getThirdPartySettings('ds');
    if (!empty($tps)) {
      foreach (array_keys($tps) as $key) {
        $display->unsetThirdPartySetting('ds', $key);
      }
    }

    // Save.
    $display->save();
  }

  /**
   * Set the label.
   */
  function entitiesSetLabelClass($label, $field_name, $text = '', $class = '', $show_colon = FALSE) {
    $edit = array(
      'fields[' . $field_name . '][label]' => $label,
    );
    if (!empty($text)) {
      $edit['fields[' . $field_name . '][settings_edit_form][third_party_settings][ds][ft][settings][lb]'] = $text;
    }
    if (!empty($class)) {
      $edit['fields[' . $field_name . '][settings_edit_form][third_party_settings][ds][ft][settings][classes][]'] = $class;
    }
    if ($show_colon) {
      $edit['fields[' . $field_name . '][settings_edit_form][third_party_settings][ds][ft][settings][lb-col]'] = '1';
    }
    $this->dsEditFormatterSettings($edit);
  }
}
