<?php

/**
 * @file
 * Definition of Drupal\yamlform\test\YamlFormSubmissionFormTest.
 */

namespace Drupal\yamlform\Tests;

use Drupal\yamlform\Entity\YamlFormSubmission;

/**
 * Tests for YAML form submission entity.
 *
 * @group YamlForm
 */
class YamlFormSubmissionTest extends YamlFormTestBase {

  /**
   * Tests YAML form submission entity.
   */
  public function testYamlFormSubmission() {
    /** @var \Drupal\yamlform\YamlFormInterface $yamlform */
    /** @var \Drupal\yamlform\Entity\YamlFormSubmission[] $submissions */
    list($yamlform, $submissions) = $this->createYamlFormWithSubmissions();

    /** @var \Drupal\yamlform\YamlFormSubmissionInterface $yamlform_submission */
    $yamlform_submission = reset($submissions);

    // Check create submission.
    $this->assert($yamlform_submission instanceof YamlFormSubmission, '$yamlform_submission instanceof YamlFormSubmission');

    // Check get YAML form.
    $this->assertEqual($yamlform_submission->getYamlForm()->id(), $yamlform->id());

    // Check get YAML source entity.
    $this->assertEqual($yamlform_submission->getSourceEntity()->id(), $yamlform->id());

    // Check get YAML source URL.
    $this->assertEqual($yamlform_submission->getSourceUrl()->toString(), $yamlform->toUrl('canonical', ['absolute' => TRUE])->toString());

    // Check get YAML source URL without uri, which will still return
    // the YAML form.
    $yamlform_submission
      ->set('uri', '')
      ->save();
    $this->assertEqual($yamlform_submission->getSourceUrl()->toString(), $yamlform->toUrl('canonical', ['absolute' => TRUE])->toString());

    // Check get YAML source URL without entity type or id, which will still
    // return the YAML form.
    $yamlform_submission
      ->set('entity_type', '')
      ->set('entity_id', '')
      ->save();
    $this->assertEqual($yamlform_submission->getSourceUrl()->toString(), $yamlform->toUrl('canonical', ['absolute' => TRUE])->toString());
    $this->assertNull($yamlform_submission->getSourceEntity(), 'YAML form submission source entity is NULL');

    // Check missing yamlform_id exception.
    try {
      YamlFormSubmission::create();
      $this->fail('YAML form id (yamlform_id) is required to create a YAML form submission.');
    }
    catch (\Exception $exception) {
      $this->pass('YAML form id (yamlform_id) is required to create a YAML form submission.');
    }

    // Check creating a submission with default data.
    $yamlform_submission = YamlFormSubmission::create(['yamlform_id' => $yamlform->id(), 'data' => ['custom' => 'value']]);
    $this->assertEqual($yamlform_submission->getData(), ['custom' => 'value']);
  }

}
