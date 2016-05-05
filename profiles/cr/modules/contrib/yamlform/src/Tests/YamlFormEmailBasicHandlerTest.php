<?php

/**
 * @file
 * Definition of Drupal\yamlform\test\YamlFormEmailBasicHandlerTest.
 */

namespace Drupal\yamlform\Tests;

use Drupal\yamlform\Entity\YamlForm;
use Drupal\yamlform\Entity\YamlFormSubmission;

/**
 * Tests for YAML form basic email functionality.
 *
 * @group YamlForm
 */
class YamlFormEmailBasicHandlerTest extends YamlFormTestBase {

  /**
   * Test basic email handler.
   */
  public function testBasicEmailHandler() {
    /** @var \Drupal\yamlform\YamlFormInterface $yamlform */
    $yamlform = YamlForm::load('test_handler_email');

    // Create a submission using the test form's default values.
    $this->drupalLogout();
    $this->postSubmission($yamlform);

    // Check sending a basic email via a submission.
    $sent_email = $this->getLastEmail();
    $this->assertEqual($sent_email['reply-to'], "from@example.com <John Smith>");
    $this->assertContains($sent_email['body'], 'Submitted by: Anonymous');
    $this->assertContains($sent_email['body'], 'First name: John');
    $this->assertContains($sent_email['body'], 'Last name: Smith');

    // Check sending a custom email using tokens.
    $this->drupalLogin($this->adminFormUser);
    $body = implode("\n", [
        'full name: [yamlform-submission:values:first_name] [yamlform-submission:values:last_name]',
        'uuid: [yamlform-submission:uuid]',
        'sid: [yamlform-submission:sid]',
        'date: [yamlform-submission:created]',
        'ip-address: [yamlform-submission:ip-address]',
        'user: [yamlform-submission:user]',
        'url: [yamlform-submission:url]',
        'edit-url: [yamlform-submission:url:edit-form]',
      ]);
    $this->drupalPostForm('admin/structure/yamlform/manage/test_handler_email/handlers/email', ['settings[message][body]' => 'custom', 'settings[message][body_custom]' => $body], t('Update'));

    $sid = $this->postSubmission($yamlform);
    /** @var \Drupal\yamlform\YamlFormSubmissionInterface $yamlform_submission */
    $yamlform_submission = YamlFormSubmission::load($sid);

    $sent_email = $this->getLastEmail();
    $this->assertContains($sent_email['body'], 'full name: John Smith');
    $this->assertContains($sent_email['body'], 'uuid: ' . $yamlform_submission->uuid->value);
    $this->assertContains($sent_email['body'], 'sid: ' . $sid);
    $this->assertContains($sent_email['body'], 'date: ' . \Drupal::service('date.formatter')->format($yamlform_submission->created->value, 'medium'));
    $this->assertContains($sent_email['body'], 'ip-address: ' . $yamlform_submission->remote_addr->value);
    $this->assertContains($sent_email['body'], 'user: ' . $this->adminFormUser->label());
    $this->assertContains($sent_email['body'], "url:");
    $this->assertContains($sent_email['body'], $yamlform_submission->toUrl('canonical', ['absolute' => TRUE])->toString());
    $this->assertContains($sent_email['body'], "edit-url:");
    $this->assertContains($sent_email['body'], $yamlform_submission->toUrl('edit-form', ['absolute' => TRUE])->toString());
  }

}
