<?php

/**
 * @file
 * Definition of Drupal\yamlform\test\YamlFormElementFormatTest.
 */

namespace Drupal\yamlform\Tests;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\user\Entity\User;
use Drupal\yamlform\Entity\YamlForm;
use Drupal\yamlform\Entity\YamlFormSubmission;
use Drupal\yamlform\YamlFormSubmissionInterface;

/**
 * Tests for YAML form submission form and inputs.
 *
 * @group YamlForm
 */
class YamlFormElementFormatTest extends YamlFormTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['system', 'node', 'user', 'yamlform', 'yamlform_test'];

  /**
   * Tests element format.
   */
  public function testElementFormat() {
    $account = User::load(1);

    /** @var \Drupal\yamlform\YamlFormInterface $yamlform_formats */
    $yamlform_formats = YamlForm::load('test_inputs_formats');
    $sid = $this->postSubmission($yamlform_formats);
    $yamlform_formats_submission = YamlFormSubmission::load($sid);

    // Check elements formatted as HTML.
    $body = $this->getMessageBody($yamlform_formats_submission, 'email_html');
    $inputs = [
      // Dates.
      'datetime (default)' => 'Tue, 08/18/2009 - 16:00',
      'datetime (html_year)' => '2009',
      'datetime (l, F j, Y - H:i)' => 'Tuesday, August 18, 2009 - 16:00',
      // Options (single and multiple).
      'checkboxes (default)' => 'one, two, three',
      'checkboxes (comma)' => 'one, two, three',
      'checkboxes (semicolon)' => 'one; two; three',
      'checkboxes (ul)' => '<div class="item-list"><ul><li>one</li><li>two</li><li>three</li></ul></div>',
      'checkboxes (ol)' => '<div class="item-list"><ol><li>one</li><li>two</li><li>three</li></ol></div>',
      // Colors.
      'color (default)' => '<span style="display:inline-block; height:1em; width:1em; border:1px solid #000; background-color:#ffffcc"></span> #ffffcc',
      'color (swatch)' => '<span style="display:inline-block; height:1em; width:1em; border:1px solid #000; background-color:#ffffcc"></span> #ffffcc',
      'color (value)' => '#ffffcc',
      // Links.
      'email (default)' => '<a href="mailto:example@example.com">example@example.com</a>',
      'email (value)' => 'example@example.com',
      'tel (default)' => '<a href="tel:999-999-9999">999-999-9999</a>',
      'tel (value)' => '999-999-9999',
      'url (default)' => '<a href="http://example.com">http://example.com</a>',
      'url (value)' => 'http://example.com',
      // Entity autocomplete.
      'entity_autocomplete (multiple)' => '<div class="item-list"><ul><li><a href="' . $account->toUrl()->setAbsolute(TRUE)->toString() . '" hreflang="en">admin</a></li>',
    ];
    foreach ($inputs as $label => $value) {
      $this->assertContains($body, '<b>' . $label . '</b><br/>' . $value, new FormattableMarkup('Found @label: @value', ['@label' => $label, '@value' => $value]));
    }

    // Check elements formatted as text.
    $body = $this->getMessageBody($yamlform_formats_submission, 'email_text');
    $inputs = [
      // Dates.
      'datetime (default): Tue, 08/18/2009 - 16:00',
      'datetime (html_year): 2009',
      'datetime (l, F j, Y - H:i): Tuesday, August 18, 2009 - 16:00',
      // Options (single and multiple).
      'checkboxes (default): one, two, three',
      'checkboxes (comma): one, two, three',
      'checkboxes (semicolon): one; two; three',
      "checkboxes (ul):\n- one\n- two\n- three",
      "checkboxes (ol):\n1. one\n2. two\n3. three",
      // Colors.
      'color (default): #ffffcc',
      'color (swatch): #ffffcc',
      'color (value): #ffffcc',
      // Links.
      'email (default): example@example.com',
      'email (value): example@example.com',
      'tel (default): 999-999-9999',
      'tel (value): 999-999-9999',
      'url (default): http://example.com',
      'url (value): http://example.com',
      // Entity autocomplete.
      "entity_autocomplete (multiple):\n- admin (1)",
    ];
    foreach ($inputs as $value) {
      $this->assertContains($body, $value, new FormattableMarkup('Found @value', ['@value' => $value]));
    }

    /** @var \Drupal\yamlform\YamlFormInterface $yamlform_formats */
    $yamlform_formats_tokens = YamlForm::load('test_inputs_formats_tokens');
    $sid = $this->postSubmission($yamlform_formats_tokens);
    $yamlform_formats_tokens_submission = YamlFormSubmission::load($sid);

    // Check elements tokens formatted as HTML.
    $body = $this->getMessageBody($yamlform_formats_tokens_submission, 'email_html');
    $inputs = [
      'default:' => 'one, two, three',
      'comma:' => 'one, two, three',
      'semicolon:' => 'one; two; three',
      'and:' => 'one, two, and three',
      'ul:' => '<div class="item-list"><ul><li>one</li><li>two</li><li>three</li></ul></div>',
      'ol:' => '<div class="item-list"><ol><li>one</li><li>two</li><li>three</li></ol></div>',
    ];
    foreach ($inputs as $label => $value) {
      $this->assertContains($body, '<h3>' . $label . '</h3>' . $value . '<hr/>', new FormattableMarkup('Found @label: @value', ['@label' => $label, '@value' => $value]));
    }

    // Check elements tokens formatted as text.
    $body = $this->getMessageBody($yamlform_formats_tokens_submission, 'email_text');
    $inputs = [
      "default:\none, two, three",
      "comma:\none, two, three",
      "semicolon:\none; two; three",
      "and:\none, two, and three",
      "ul:\n- one\n- two\n- three",
      "ol:\n1. one\n2. two\n3. three",
    ];
    foreach ($inputs as $value) {
      $this->assertContains($body, $value, new FormattableMarkup('Found @value', ['@value' => $value]));
    }

    // Check element default format global setting.
    \Drupal::configFactory()->getEditable('yamlform.settings')
      ->set('format.checkboxes', 'and')
      ->save();
    $body = $this->getMessageBody($yamlform_formats_tokens_submission, 'email_text');
    $this->assertContains($body, "default:\none, two, and three", new FormattableMarkup('Found @value', ['@value' => $value]));
  }

  /**
   * Get YAML form email message body for a YAML form submission.
   *
   * @param \Drupal\yamlform\YamlFormSubmissionInterface $submission
   *   A YAML form submission.
   * @param string $handler_id
   *   The YAML form email handler id.
   *
   * @return string
   *   The YAML form email message body for a YAML form submission.
   */
  protected function getMessageBody(YamlFormSubmissionInterface $submission, $handler_id = 'email_html') {
    /** @var \Drupal\yamlform\YamlFormHandlerMessageInterface $message_handler */
    $message_handler = $submission->getYamlForm()->getHandler($handler_id);
    $message = $message_handler->getMessage($submission);
    $body = (string) $message['body'];
    $this->verbose($body);
    return $body;
  }

}
