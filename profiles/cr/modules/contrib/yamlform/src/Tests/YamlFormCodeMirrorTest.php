<?php

/**
 * @file
 * Definition of Drupal\yamlform\test\YamlFormCodeMirrorTest.
 */

namespace Drupal\yamlform\Tests;

use Drupal\simpletest\WebTestBase;
use Drupal\yamlform\Entity\YamlForm;

/**
 * Tests for YAML form CodeMirror element.
 *
 * @group YamlForm
 */
class YamlFormCodeMirrorTest extends WebTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['system', 'block', 'node', 'user', 'yamlform', 'yamlform_test'];

  /**
   * Test CodeMirror element.
   */
  public function testCodeMirrorElement() {
    // @see yamlform.yamlform.test_element_codemirror.yml.
    /** @var \Drupal\yamlform\YamlFormInterface $yamlform */
    $yamlform = YamlForm::load('test_element_codemirror');

    /* YAML */

    $this->drupalGet('yamlform/' . $yamlform->id());

    // Check if CodeMirror library using local or CDN.
    // This all depends on if CodeMirror is installed locally.
    if (file_exists(DRUPAL_ROOT . '/libraries/codemirror')) {
      $this->assertRaw('/libraries/codemirror/lib/codemirror.js');
    }
    else {
      $this->assertRaw('https://cdnjs.cloudflare.com/ajax/libs/codemirror/');
    }

    // Check for YAML textarea with yamlform-codemirror class name.
    $this->assertRaw('<textarea data-drupal-selector="edit-yaml" class="js-yamlform-codemirror yamlform-codemirror yaml form-textarea resize-vertical" data-yamlform-codemirror-mode="text/x-yaml" id="edit-yaml" name="yaml" rows="5" cols="60"></textarea>');

    // Check submitting invalid YAML.
    $this->drupalPostForm('yamlform/' . $yamlform->id(), ['yaml' => "not\nvalid\nyaml"], t('Submit'));
    $this->assertFieldByName('yaml', "not\nvalid\nyaml");
    $this->assertRaw('<em class="placeholder">YAML</em>');
    $this->assertRaw('<li>Unable to parse at line 1 (near &quot;not&quot;).</li>');

    // Check submitting valid YAML.
    $this->drupalPostForm('yamlform/' . $yamlform->id(), ['yaml' => "valid: yaml"], t('Submit'));
    $this->assertRaw('<a href="' . $yamlform->toUrl()->toString() . '">' . t('Back to form') . '</a>');
    $this->assertNoRaw('<em class="placeholder">YAML</em> is not valid.');

    /* HTML */
    $this->drupalGet('yamlform/' . $yamlform->id());

    // Check for HTML textarea with yamlform-codemirror class name.
    $this->assertRaw('<textarea data-drupal-selector="edit-html" class="js-yamlform-codemirror yamlform-codemirror html form-textarea resize-vertical" data-yamlform-codemirror-mode="text/html" id="edit-html" name="html" rows="5" cols="60"></textarea>');

    // Check submitting invalid HTML.
    $this->drupalPostForm('yamlform/' . $yamlform->id(), ['html' => "<b>not valid</em>"], t('Submit'));
    $this->assertFieldByName('html', "<b>not valid</em>");
    $this->assertRaw('<em class="placeholder">HTML</em> is not valid.');
    $this->assertRaw('<li>Opening and ending tag mismatch: b line 1 and em');

    // Check submitting valid HTML.
    $this->drupalPostForm('yamlform/' . $yamlform->id(), ['html' => "<b>valid</b>"], t('Submit'));
    $this->assertRaw('<a href="' . $yamlform->toUrl()->toString() . '">' . t('Back to form') . '</a>');
    $this->assertNoRaw('<em class="placeholder">HTML</em> is not valid.');
  }

}
