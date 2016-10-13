<?php

namespace Drupal\yamlform\Tests;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\user\Entity\User;
use Drupal\yamlform\Entity\YamlForm;
use Drupal\yamlform\Entity\YamlFormSubmission;

/**
 * Tests for form submission view as HTML, YAML, and plain text.
 *
 * @group YamlForm
 */
class YamlFormSubmissionViewTest extends YamlFormTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['system', 'block', 'filter', 'node', 'user', 'yamlform', 'yamlform_test'];

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->createFilters();
  }

  /**
   * Tests view submissions.
   */
  public function testView() {
    $account = User::load(1);

    $yamlform_element = YamlForm::load('test_element');
    $sid = $this->postSubmission($yamlform_element);
    $submission = YamlFormSubmission::load($sid);

    $this->drupalLogin($this->adminSubmissionUser);

    $this->drupalGet('admin/structure/yamlform/manage/test_element/submission/' . $submission->id());

    // Check displayed values.
    $elements = [
      'hidden' => '{hidden}',
      'value' => '{value}',
      'textarea' => "{textarea line 1}<br />\n{textarea line 2}",
      'textfield' => '{textfield}',
      'select' => 'one',
      // @todo: Fix broken test.
      // 'select_multiple' => 'one, two',
      'checkbox' => 'Yes',
      'checkboxes' => 'one, two',
      'radios' => 'Yes',
      'email' => '<a href="mailto:example@example.com">example@example.com</a>',
      'number' => '1',
      'range' => '1',
      'tel' => '<a href="tel:999-999-9999">999-999-9999</a>',
      'url' => '<a href="http://example.com">http://example.com</a>',
      'color' => '<span style="display:inline-block; height:1em; width:1em; border:1px solid #000; background-color:#ffffcc"></span> #ffffcc',
      'weight' => '0',
      'date' => 'Tuesday, August 18, 2009',
      'datetime' => 'Tuesday, August 18, 2009 - 4:00 PM',
      'datelist' => 'Tuesday, August 18, 2009 - 4:00 PM',
      'dollars' => '$100.00',
      'text_format' => '<p>The quick brown fox jumped over the lazy dog.</p>',
      'entity_autocomplete (user)' => '<a href="' . $account->toUrl()->setAbsolute(TRUE)->toString() . '" hreflang="en">admin</a>',
      'language_select' => 'English (en)',
    ];
    foreach ($elements as $label => $value) {
      $this->assertRaw('<b>' . $label . '</b><br/>' . $value, new FormattableMarkup('Found @label: @value', ['@label' => $label, '@value' => $value]));
    }

    // Check details element.
    $this->assertRaw('<summary role="button" aria-expanded="true" aria-pressed="true">Standard Elements</summary>');

    // Check empty details element removed.
    $this->assertNoRaw('<summary role="button" aria-expanded="true" aria-pressed="true">Markup Elements</summary>');
  }

}
