<?php

/**
 * @ingroup diff
 */

namespace Drupal\diff\Tests;

/**
 * Tests the Diff admin forms.
 *
 * @group diff
 */
class AdminFormsTest extends DiffTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'field_ui',
    'help',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->drupalLogin($this->rootUser);
  }

  /**
   * Tests the descriptions in the Settings UI.
   */
  public function testSettingsUI() {
    // Enable the help block.
    $this->drupalPlaceBlock('help_block', ['region' => 'help']);

    // Create a user with administer permissions.
    $permissions = [
      'administer site configuration',
      'access administration pages',
      'administer blocks',
    ];
    $admin_user = $this->drupalCreateUser($permissions);
    $this->drupalLogin($admin_user);

    $this->drupalGet('admin/config/content/diff/general');
    // Check the settings introduction text.
    $this->assertText('Configurations for the revision comparison functionality and diff layout plugins.');
    // Check the layout plugins descriptions.
    $this->assertText('Field based layout, displays revision comparison side by side.');
    $this->assertText('Field based layout, displays revision comparison line by line.');
  }

  /**
   * Tests the Settings tab.
   */
  public function testSettingsTab() {
    $edit = [
      'radio_behavior' => 'linear',
      'context_lines_leading' => 10,
      'context_lines_trailing' => 5,
    ];
    $this->drupalPostForm('admin/config/content/diff/general', $edit, t('Save configuration'));
    $this->assertText('The configuration options have been saved.');
  }

  /**
   * Tests the module requirements.
   */
  public function testRequirements() {
    module_load_install('diff');
    $requirements = diff_requirements('runtime');
    $this->assertEqual($requirements['html_diff_advanced']['title'], 'Diff');
    $this->assertEqual($requirements['html_diff_advanced']['value'], 'Visual inline layout');
  }

  /**
   * Tests the Configurable Fields tab.
   */
  public function testConfigurableFieldsTab() {
    $this->drupalGet('admin/config/content/diff/fields');
    $this->drupalPostAjaxForm(NULL, [], 'node.body_settings_edit');
    $this->assertText('Plugin settings: Text');
    $edit = [
      'fields[node.body][settings_edit_form][settings][show_header]' => TRUE,
      'fields[node.body][settings_edit_form][settings][compare_format]' => TRUE,
      'fields[node.body][settings_edit_form][settings][markdown]' => 'filter_xss_all',
    ];
    $this->drupalPostForm(NULL, $edit, t('Update'));
    $this->drupalPostForm(NULL, [], t('Save'));
    $this->assertText('Your settings have been saved.');
  }

  /**
   * Tests the Compare Revisions vertical tab.
   */
  public function testPluginWeight() {
    // Create a node with a revision.
    $edit = [
      'title[0][value]' => 'great_title',
      'body[0][value]' => '<p>great_body</p>',
    ];
    $this->drupalPostForm('node/add/article', $edit, t('Save and publish'));
    $this->clickLink('Edit');
    $edit = [
      'title[0][value]' => 'greater_title',
      'body[0][value]' => '<p>greater_body</p>',
    ];
    $this->drupalPostForm(NULL, $edit, t('Save and keep published'));

    // Assert the diff display uses the classic layout.
    $node = $this->getNodeByTitle('greater_title');
    $this->drupalGet('node/' . $node->id() . '/revisions');
    $this->drupalPostForm(NULL, [], t('Compare'));
    $this->assertLink('Unified fields');
    $this->assertLink('Split fields');
    $this->assertLink('Raw');
    $this->assertLink('Strip tags');
    $text = $this->xpath('//tbody/tr[4]/td[3]');
    $this->assertEqual(htmlspecialchars_decode(strip_tags($text[0]->asXML())), '<p>great_body</p>');

    // Change the settings of the layouts, disable the single column.
    $edit = [
      'layout_plugins[split_fields][weight]' => '11',
      'layout_plugins[unified_fields][enabled]' => FALSE,
    ];
    $this->drupalPostForm('admin/config/content/diff/general', $edit, t('Save configuration'));

    // Assert the diff display uses the markdown layout.
    $this->drupalGet('node/' . $node->id() . '/revisions');
    $this->drupalPostForm(NULL, [], t('Compare'));
    $this->assertResponse(200);
    $this->assertNoLink('Unified fields');
    $this->assertLink('Split fields');
    $this->clickLink('Split fields');
    $this->assertLink('Raw');
    $this->assertLink('Strip tags');
    $this->clickLink('Strip tags');
    $text = $this->xpath('//tbody/tr[4]/td[2]');
    $this->assertEqual(htmlspecialchars_decode(strip_tags($text[0]->asXML())), 'great_body');

    // Change the settings of the layouts, enable single column.
    $edit = [
      'layout_plugins[unified_fields][enabled]' => TRUE,
      'layout_plugins[split_fields][enabled]' => FALSE,
    ];
    $this->drupalPostForm('admin/config/content/diff/general', $edit, t('Save configuration'));

    // Test the validation of form.
    $edit = [
      'layout_plugins[unified_fields][enabled]' => FALSE,
      'layout_plugins[split_fields][enabled]' => FALSE,
    ];
    $this->drupalPostForm('admin/config/content/diff/general', $edit, t('Save configuration'));
    $this->assertText('At least one layout plugin needs to be enabled.');

    // Assert the diff display uses the single column layout.
    $this->drupalGet('node/' . $node->id() . '/revisions');
    $this->drupalPostForm(NULL, [], t('Compare'));
    $this->assertResponse(200);
    $this->assertLink('Unified fields');
    $this->assertNoLink('Split fields');
    $this->assertLink('Raw');
    $this->assertLink('Strip tags');
    $text = $this->xpath('//tbody/tr[5]/td[4]');
    $this->assertEqual(htmlspecialchars_decode(strip_tags($text[0]->asXML())), '<p>great_body</p>');
    $this->clickLink('Strip tags');
    $text = $this->xpath('//tbody/tr[5]/td[2]');
    $this->assertEqual(htmlspecialchars_decode(strip_tags($text[0]->asXML())), 'great_body');
  }

}
