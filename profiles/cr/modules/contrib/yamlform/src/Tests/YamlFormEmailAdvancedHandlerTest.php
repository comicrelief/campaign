<?php

namespace Drupal\yamlform\Tests;

use Drupal\yamlform\Entity\YamlForm;

/**
 * Tests for form advanced email functionality with HTML and attachments.
 *
 * @group YamlForm
 */
class YamlFormEmailAdvancedHandlerTest extends YamlFormTestBase {

  public static $modules = ['system', 'block', 'filter', 'node', 'user', 'file', 'yamlform', 'yamlform_test'];

  /**
   * Create form test users.
   */
  protected function createUsers() {
    // Create filter.
    $this->createFilters();

    $this->normalUser = $this->drupalCreateUser([
      'access user profiles',
      'access content',
      $this->basicHtmlFilter->getPermissionName(),
    ]);
    $this->adminFormUser = $this->drupalCreateUser([
      'access user profiles',
      'access content',
      'administer yamlform',
      'administer blocks',
      'administer nodes',
      'administer users',
      $this->basicHtmlFilter->getPermissionName(),
    ]);
    $this->adminSubmissionUser = $this->drupalCreateUser([
      'access user profiles',
      'access content',
      'administer yamlform submission',
      $this->basicHtmlFilter->getPermissionName(),
    ]);
  }

  /**
   * Test advanced email handler.
   *
   * Note:
   * The TestMailCollector extends PhpMail, therefore the HTML body
   * will still be escaped, which is why we are looking at the params.body.
   *
   * @see \Drupal\Core\Mail\Plugin\Mail\TestMailCollector
   */
  public function testAdvancedEmailHandler() {
    /** @var \Drupal\yamlform\YamlFormInterface $yamlform_email_advanced */
    $yamlform_email_advanced = YamlForm::load('test_handler_email_advanced');

    // Generate a test submission with a file upload.
    $this->drupalLogin($this->adminFormUser);

    // Post a new submission using test form which will automatically
    // upload file.txt.
    $edit = [
      'first_name' => 'John',
      'last_name' => 'Smith',
      'email' => 'from@example.com',
      'subject' => 'Subject',
      'message[value]' => '<p><em>Please enter a message.</em> Test that double "quotes" are not encoded.</p>',
    ];
    $this->drupalPostForm('yamlform/' . $yamlform_email_advanced->id() . '/test', $edit, t('Submit'));
    $sid = $this->getLastSubmissionId($yamlform_email_advanced);
    $sent_mail = $this->getLastEmail();

    // Check email is HTML.
    $this->assertContains($sent_mail['params']['body'], '<b>First name</b><br/>John<br/><br/>');
    $this->assertContains($sent_mail['params']['body'], '<b>Last name</b><br/>Smith<br/><br/>');
    $this->assertContains($sent_mail['params']['body'], '<b>Email</b><br/><a href="mailto:from@example.com">from@example.com</a><br/><br/>');
    $this->assertContains($sent_mail['params']['body'], '<b>Subject</b><br/>Subject<br/><br/>');
    $this->assertContains($sent_mail['params']['body'], '<b>Message</b><br/><p><em>Please enter a message.</em> Test that double "quotes" are not encoded.</p><br/><br/>');

    // Check email has attachment.
    $this->assertEqual($sent_mail['params']['attachments'][0]['filecontent'], '{empty}');
    $this->assertEqual($sent_mail['params']['attachments'][0]['filename'], 'file.txt');
    $this->assertEqual($sent_mail['params']['attachments'][0]['filemime'], 'text/plain');

    // Check resend form includes link to the attachment.
    $this->drupalGet("admin/structure/yamlform/manage/test_handler_email_advanced/submission/$sid/resend");
    $this->assertRaw('<span class="file file--mime-text-plain file--text">');
    $this->assertRaw('file.txt');

    // Check resend form with custom message.
    $this->drupalPostForm("admin/structure/yamlform/manage/test_handler_email_advanced/submission/$sid/resend", ['message[body]' => 'Testing 123...'], t('Resend message'));
    $sent_mail = $this->getLastEmail();
    $this->assertNotContains($sent_mail['params']['body'], '<b>First name</b><br/>John<br/><br/>');
    $this->assertEqual($sent_mail['params']['body'], 'Testing 123...');

    // Check resent email has the same attachment.
    $this->assertEqual($sent_mail['params']['attachments'][0]['filecontent'], '{empty}');
    $this->assertEqual($sent_mail['params']['attachments'][0]['filename'], 'file.txt');
    $this->assertEqual($sent_mail['params']['attachments'][0]['filemime'], 'text/plain');
  }

}
