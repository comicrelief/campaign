<?php

namespace Drupal\yamlform_node\Tests;

use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\yamlform\Entity\YamlForm;
use Drupal\yamlform\Entity\YamlFormSubmission;
use Drupal\yamlform\Tests\YamlFormTestBase;

/**
 * Tests for form node.
 *
 * @group YamlFormNode
 */
class YamlFormNodeTest extends YamlFormTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['system', 'block', 'node', 'user', 'yamlform', 'yamlform_test', 'yamlform_node'];

  /**
   * Tests form node.
   */
  public function testNode() {
    // Create node.
    $node = $this->drupalCreateNode(['type' => 'yamlform']);

    // Check contact form.
    $node->yamlform->target_id = 'contact';
    $node->yamlform->status = 1;
    $node->save();
    $this->drupalGet('node/' . $node->id());
    $this->assertRaw('yamlform-submission-contact-form');
    $this->assertNoFieldByName('name', 'John Smith');

    // Check contact form with default data.
    $node->yamlform->default_data = "name: 'John Smith'";
    $node->save();
    $this->drupalGet('node/' . $node->id());
    $this->assertFieldByName('name', 'John Smith');

    /* Form closed */

    // Check contact form closed.
    $node->yamlform->status = 0;
    $node->save();
    $this->drupalGet('node/' . $node->id());
    $this->assertNoFieldByName('name', 'John Smith');
    $this->assertRaw('Sorry...This form is closed to new submissions.');

    /* Confirmation inline (test_confirmation_inline) */

    // Check confirmation inline form.
    $node->yamlform->target_id = 'test_confirmation_inline';
    $node->yamlform->default_data = '';
    $node->yamlform->status = 1;
    $node->save();
    $this->drupalPostForm('node/' . $node->id(), [], t('Submit'));
    $this->assertRaw('This is a custom inline confirmation message.');

    /* Submission limit (test_submission_limit) */

    // Set per entity total and user limit.
    // @see \Drupal\yamlform\Tests\YamlFormSubmissionFormSettingsTest::testSettings
    $node->yamlform->target_id = 'test_submission_limit';
    $node->yamlform->default_data = '';
    $node->save();

    $limit_form = YamlForm::load('test_submission_limit');
    $limit_form->setSettings([
      'limit_total' => NULL,
      'limit_user' => NULL,
      'entity_limit_total' => 3,
      'entity_limit_user' => 1,
      'limit_total_message' => 'Only 3 submissions are allowed.',
      'limit_user_message' => 'You are only allowed to have 1 submission for this form.',
    ]);
    $limit_form->save();

    // Check per entity user limit.
    $this->drupalLogin($this->normalUser);
    $this->drupalPostForm('node/' . $node->id(), [], t('Submit'));
    $this->drupalGet('node/' . $node->id());
    $this->assertNoFieldByName('op', 'Submit');
    $this->assertRaw('You are only allowed to have 1 submission for this form.');
    $this->drupalLogout();

    // Check per entity total limit.
    $this->drupalPostForm('node/' . $node->id(), [], t('Submit'));
    $this->drupalPostForm('node/' . $node->id(), [], t('Submit'));
    $this->drupalGet('node/' . $node->id());
    $this->assertNoFieldByName('op', 'Submit');
    $this->assertRaw('Only 3 submissions are allowed.');
    $this->assertNoRaw('You are only allowed to have 1 submission for this form.');

    /* Prepopulate source entity */

    $yamlform_contact = YamlForm::load('contact');

    $node->yamlform->target_id = 'contact';
    $node->yamlform->status = 1;
    $node->yamlform->default_data = "name: '{name}'";
    $node->save();

    $source_entity_options = ['query' => ['source_entity_type' => 'node', 'source_entity_id' => $node->id()]];

    // Check default data from source entity using query string.
    $this->drupalGet('yamlform/contact', $source_entity_options);
    $this->assertFieldByName('name', '{name}');

    // Check prepopulating source entity using query string.
    $edit = [
      'name' => 'name',
      'email' => 'example@example.com',
      'subject' => 'subject',
      'message' => 'message',
    ];
    $this->drupalPostForm('yamlform/contact', $edit, t('Send message'), $source_entity_options);
    $sid = $this->getLastSubmissionId($yamlform_contact);
    $submission = YamlFormSubmission::load($sid);
    $this->assertNotNull($submission->getSourceEntity());
    if ($submission->getSourceEntity()) {
      $this->assertEqual($submission->getSourceEntity()
        ->getEntityTypeId(), 'node');
      $this->assertEqual($submission->getSourceEntity()->id(), $node->id());
    }

    /* Check displaying link to form */

    // Set form reference to be displayed as a link.
    $display_options = [
      'type' => 'yamlform_entity_reference_link',
      'settings' => [
        'label' => 'Register',
      ],
    ];
    $view_display = EntityViewDisplay::load('node.yamlform.default');
    $view_display->setComponent('yamlform', $display_options)->save();

    // Set default data.
    $node->yamlform->target_id = 'contact';
    $node->yamlform->status = 1;
    $node->yamlform->default_data = "name: '{name}'";
    $node->save();

    // Check 'Register' link.
    $this->drupalGet('node/' . $node->id());
    $this->assertLink('Register');

    // Check that link include source_entity_type and source_entity_id.
    $this->assertLinkByHref($yamlform_contact->toUrl('canonical', $source_entity_options)->toString());
  }

}
