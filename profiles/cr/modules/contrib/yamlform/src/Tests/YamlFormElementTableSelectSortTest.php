<?php

namespace Drupal\yamlform\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Tests for form table select sort element.
 *
 * @group YamlForm
 */
class YamlFormElementTableSelectSortTest extends WebTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['system', 'user', 'yamlform', 'yamlform_test'];

  /**
   * Tests building of options elements.
   */
  public function test() {

    /**************************************************************************/
    // Processing.
    /**************************************************************************/

    $edit = [
      'yamlform_tableselect_sort_default[one][weight]' => '4',
      'yamlform_tableselect_sort_default[two][weight]' => '3',
      'yamlform_tableselect_sort_default[three][weight]' => '2',
      'yamlform_tableselect_sort_default[four][weight]' => '1',
      'yamlform_tableselect_sort_default[five][weight]' => '0',
      'yamlform_tableselect_sort_default[one][checkbox]' => TRUE,
      'yamlform_tableselect_sort_default[two][checkbox]' => TRUE,
      'yamlform_tableselect_sort_default[three][checkbox]' => TRUE,
      'yamlform_tableselect_sort_default[four][checkbox]' => TRUE,
      'yamlform_tableselect_sort_default[five][checkbox]' => TRUE,
    ];
    $this->drupalPostForm('yamlform/test_element_tableselect_sort', $edit, t('Submit'));
    $this->assertRaw("yamlform_tableselect_sort_default:
  - five
  - four
  - three
  - two
  - one
yamlform_tableselect_sort_custom:
  - five
  - three");
  }

}
