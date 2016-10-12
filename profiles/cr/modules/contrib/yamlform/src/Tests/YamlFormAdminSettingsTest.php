<?php

namespace Drupal\yamlform\Tests;

/**
 * Tests for form entity.
 *
 * @group YamlForm
 */
class YamlFormAdminSettingsTest extends YamlFormTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['system', 'block', 'node', 'user', 'yamlform', 'yamlform_ui', 'yamlform_test'];

  /**
   * Tests form admin settings.
   */
  public function testAdminSettings() {
    global $base_path;

    $this->drupalLogin($this->adminFormUser);

    /* Elements */

    // Check that description is 'after' the element.
    $this->drupalGet('yamlform/test_element');
    $this->assertPattern('#\{item title\}.+\{item markup\}.+\{item description\}#ms');

    // Set the default description display to 'before'.
    $this->drupalPostForm('admin/structure/yamlform/settings', ['elements[default_description_display]' => 'before'], t('Save configuration'));

    // Check that description is 'before' the element.
    $this->drupalGet('yamlform/test_element');
    $this->assertNoPattern('#\{item title\}.+\{item markup\}.+\{item description\}#ms');
    $this->assertPattern('#\{item title\}.+\{item description\}.+\{item markup\}#ms');

    /* UI disable dialog */

    // Check that dialogs are enabled.
    $this->drupalGet('admin/structure/yamlform');
    $this->assertRaw('<a href="' . $base_path . 'admin/structure/yamlform/add" class="button button-action button--primary button--small use-ajax" data-dialog-type="modal" data-dialog-options="{&quot;width&quot;:400}">Add form</a>');

    // Disable dialogs.
    $this->drupalPostForm('admin/structure/yamlform/settings', ['ui[dialog_disabled]' => TRUE], t('Save configuration'));

    // Check that dialogs are disabled. (ie use-ajax is not included)
    $this->drupalGet('admin/structure/yamlform');
    $this->assertNoRaw('<a href="' . $base_path . 'admin/structure/yamlform/add" class="button button-action button--primary button--small use-ajax" data-dialog-type="modal" data-dialog-options="{&quot;width&quot;:400}">Add form</a>');
    $this->assertRaw('<a href="' . $base_path . 'admin/structure/yamlform/add" class="button button-action button--primary button--small">Add form</a>');

    /* UI disable html editor */

    // Check that HTML editor is enabled.
    $this->drupalGet('yamlform/test_element_html_editor');
    $this->assertRaw('<textarea data-drupal-selector="edit-yamlform-html-editor" id="edit-yamlform-html-editor" name="yamlform_html_editor" rows="5" cols="60" class="form-textarea resize-vertical">Hello &lt;b&gt;World!!!&lt;/b&gt;</textarea>');

    // Disable HTML editor.
    $this->drupalPostForm('admin/structure/yamlform/settings', ['ui[html_editor_disabled]' => TRUE], t('Save configuration'));

    // Check that HTML editor is removed and replaced by CodeMirror HTML editor.
    $this->drupalGet('yamlform/test_element_html_editor');
    $this->assertNoRaw('<textarea data-drupal-selector="edit-yamlform-html-editor" id="edit-yamlform-html-editor" name="yamlform_html_editor" rows="5" cols="60" class="form-textarea resize-vertical">Hello &lt;b&gt;World!!!&lt;/b&gt;</textarea>');
    $this->assertRaw('<textarea data-drupal-selector="edit-yamlform-html-editor" class="js-yamlform-codemirror yamlform-codemirror html form-textarea resize-vertical" data-yamlform-codemirror-mode="text/html" id="edit-yamlform-html-editor" name="yamlform_html_editor" rows="5" cols="60">Hello &lt;b&gt;World!!!&lt;/b&gt;</textarea>');

  }

}
