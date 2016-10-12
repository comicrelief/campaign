<?php

namespace Drupal\yamlform\Tests;

use Drupal\yamlform\Entity\YamlFormSubmission;

/**
 * Tests for form submission entity.
 *
 * @group YamlForm
 */
class YamlFormSubmissionTest extends YamlFormTestBase {

  /**
   * Tests form submission entity.
   */
  public function testYamlFormSubmission() {
    /** @var \Drupal\yamlform\YamlFormInterface $yamlform */
    /** @var \Drupal\yamlform\YamlFormSubmissionInterface[] $submissions */
    list($yamlform, $submissions) = $this->createYamlFormWithSubmissions();

    /** @var \Drupal\yamlform\YamlFormSubmissionInterface $yamlform_submission */
    $yamlform_submission = reset($submissions);

    // Check create submission.
    $this->assert($yamlform_submission instanceof YamlFormSubmission, '$yamlform_submission instanceof YamlFormSubmission');

    // Check get form.
    $this->assertEqual($yamlform_submission->getYamlForm()->id(), $yamlform->id());

    // Check that YAML source entity is NULL.
    $this->assertNull($yamlform_submission->getSourceEntity());

    // Check get YAML source URL without uri, which will still return
    // the form.
    $yamlform_submission
      ->set('uri', '')
      ->save();
    $this->assertEqual($yamlform_submission->getSourceUrl()->toString(), $yamlform->toUrl('canonical', ['absolute' => TRUE])->toString());

    // Check get YAML source URL set to user 1.
    $this->createUsers();
    $yamlform_submission
      ->set('entity_type', 'user')
      ->set('entity_id', $this->normalUser->id())
      ->save();
    $this->assertEqual($yamlform_submission->getSourceUrl()->toString(), $this->normalUser->toUrl('canonical', ['absolute' => TRUE])->toString());

    // Check missing yamlform_id exception.
    try {
      YamlFormSubmission::create();
      $this->fail('Form id (yamlform_id) is required to create a form submission.');
    }
    catch (\Exception $exception) {
      $this->pass('Form id (yamlform_id) is required to create a form submission.');
    }

    // Check creating a submission with default data.
    $yamlform_submission = YamlFormSubmission::create(['yamlform_id' => $yamlform->id(), 'data' => ['custom' => 'value']]);
    $this->assertEqual($yamlform_submission->getData(), ['custom' => 'value']);

    // Check submission label.
    $yamlform_submission->save();
    $this->assertEqual($yamlform_submission->label(), $yamlform->label() . ': Submission #' . $yamlform_submission->serial());
  }

}
