<?php

/**
 * @file
 * Definition of Drupal\ds\Tests\ExportablesTest.
 */

namespace Drupal\ds\Tests;
use Drupal\Core\Entity\Entity\EntityViewDisplay;

/**
 * Tests for exportables in Display Suite.
 *
 * @group ds
 */
class ExportablesTest extends FastTestBase {

  /**
   * Enables the exportables module.
   */
  function dsExportablesSetup() {
    /** @var $display EntityViewDisplay */
    $display = EntityViewDisplay::load('node.article.default');
    $display->delete();
    \Drupal::service('module_installer')->install(array('ds_exportables_test'));
  }

  // Test layout and field settings configuration.
  function testDSExportablesLayoutFieldsettings() {
    $this->dsExportablesSetup();

    // Look for default custom field.
    $this->drupalGet('admin/structure/ds/fields');
    $this->assertText('Exportable field');
    $this->drupalGet('admin/structure/types/manage/article/display');
    $this->assertText('Exportable field');

    $settings = array(
      'type' => 'article',
      'title' => 'Exportable'
    );
    $node = $this->drupalCreateNode($settings);
    $this->drupalGet('node/' . $node->id());
    $this->assertRaw('group-left', 'Left region found');
    $this->assertRaw('group-right', 'Right region found');
    $this->assertNoRaw('group-header', 'No header region found');
    $this->assertNoRaw('group-footer', 'No footer region found');
    $link = $this->xpath('//h3/a[text()=:text]', array(
      ':text' => 'Exportable',
    ));
    $this->assertEqual(count($link), 1, 'Default title with h3 found');
    $link = $this->xpath('//a[text()=:text]', array(
      ':text' => 'Read more',
    ));
    $this->assertEqual(count($link), 1, 'Default read more found');

    // Override default layout.
    $layout = array(
      'layout' => 'ds_2col_stacked',
    );

    $assert = array(
      'regions' => array(
        'header' => '<td colspan="8">' . t('Header') . '</td>',
        'left' => '<td colspan="8">' . t('Left') . '</td>',
        'right' => '<td colspan="8">' . t('Right') . '</td>',
        'footer' => '<td colspan="8">' . t('Footer') . '</td>',
      ),
    );

    $fields = array(
      'fields[node_post_date][region]' => 'header',
      'fields[node_author][region]' => 'left',
      'fields[node_link][region]' => 'left',
      'fields[body][region]' => 'right',
    );

    $this->dsSelectLayout($layout, $assert);
    $this->dsConfigureUI($fields);

    $this->drupalGet('node/' . $node->id());
    $this->assertRaw('group-left', 'Left region found');
    $this->assertRaw('group-right', 'Right region found');
    $this->assertRaw('group-header', 'Header region found');
    $this->assertRaw('group-footer', 'Footer region found');
  }

}
