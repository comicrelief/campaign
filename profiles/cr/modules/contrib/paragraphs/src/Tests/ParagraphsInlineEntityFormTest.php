<?php

namespace Drupal\paragraphs\Tests;

/**
 * Tests the configuration of paragraphs in relation to ief.
 *
 * @group paragraphs
 */
class ParagraphsInlineEntityFormTest extends ParagraphsTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'inline_entity_form',
  ];

  /**
   * Tests the revision of paragraphs.
   */
  public function testParagraphsIEFPreview() {
    // Create article content type with a paragraphs field.
    $this->addParagraphedContentType('article', 'field_paragraphs');
    $this->loginAsAdmin(['create article content', 'edit any article content']);

    // Create the paragraphs type simple.
    $this->addParagraphsType('simple');

    // Create a reference to an article.
    $this->fieldUIAddNewField('admin/structure/paragraphs_type/simple', 'article', 'Article', 'field_ui:entity_reference:node', [
      'settings[target_type]' => 'node',
      'cardinality' => 'number',
      'cardinality_number' => 1,
    ], [
      'required' => TRUE,
      'settings[handler_settings][target_bundles][article]' => TRUE
    ]);

    // Enable IEF simple widget.
    $this->drupalGet('admin/structure/paragraphs_type/simple/form-display');
    $edit = [
      'fields[field_article][type]' => 'inline_entity_form_simple',
    ];
    $this->drupalPostForm(NULL, $edit, t('Save'));

    // Set the paragraphs widget mode to preview.
    $this->setParagraphsWidgetMode('article', 'field_paragraphs', 'preview');

    // Create node with one paragraph.
    $this->drupalGet('node/add/article');
    $this->drupalPostAjaxForm(NULL, [], 'field_paragraphs_simple_add_more');

    // Set the values and save.
    $edit = [
      'title[0][value]' => 'Dummy1',
      'field_paragraphs[0][subform][field_article][0][inline_entity_form][title][0][value]' => 'Dummy2',
    ];
    $this->drupalPostForm(NULL, $edit, t('Save and publish'));

    // Go back into edit page.
    $node = $this->getNodeByTitle('Dummy1');
    $this->drupalGet('node/' . $node->id() . '/edit');

    // Try to open the previewed paragraph.
    $this->drupalPostAjaxForm(NULL, [], 'field_paragraphs_0_edit');
  }

  /**
   * Sets the Paragraphs widget display mode.
   *
   * @param string $content_type
   *   Content type name where to set the widget mode.
   * @param string $paragraphs_field
   *   Paragraphs field to change the mode.
   * @param string $mode
   *   Mode to be set. ('closed', 'preview' or 'open').
   */
  protected function setParagraphsWidgetMode($content_type, $paragraphs_field, $mode) {
    $this->drupalGet('admin/structure/types/manage/' . $content_type . '/form-display');
    $this->drupalPostAjaxForm(NULL, [], $paragraphs_field . '_settings_edit');
    $this->drupalPostForm(NULL, ['fields[' . $paragraphs_field . '][settings_edit_form][settings][edit_mode]' => $mode], t('Update'));
    $this->drupalPostForm(NULL, [], 'Save');
  }
}
