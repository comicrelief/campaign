<?php

/**
 * @file
 * Definition of Drupal\yamlform\Tests\YamlFormManagedFileTest.
 */

namespace Drupal\yamlform\Tests;

use Drupal\file\Entity\File;
use Drupal\yamlform\Entity\YamlForm;
use Drupal\yamlform\Entity\YamlFormSubmission;

/**
 * Test for YAML form managed file handling.
 *
 * @group YamlForm
 */
class YamlFormManagedFileTest extends YamlFormTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['system', 'block', 'file', 'node', 'user', 'yamlform', 'yamlform_test'];

  /**
   * Test managed file.
   */
  public function testManagedFile() {
    /** @var \Drupal\file\FileUsage\FileUsageInterface $file_usage */
    $file_usage = $this->container->get('file.usage');

    /** @var \Drupal\yamlform\YamlFormInterface $yamlform */
    $yamlform = YamlForm::load('test_inputs_managed_file');

    // Create test files.
    $test_files = $this->drupalGetTestFiles('text');
    $this->verbose('<pre>' . print_r($test_files, TRUE) . '</pre>');

    // Upload file.
    $edit = [
      'files[file]' => \Drupal::service('file_system')->realpath($test_files[0]->uri),
    ];
    $sid = $this->postSubmission($yamlform, $edit);

    /** @var \Drupal\yamlform\YamlFormSubmissionInterface $submission */
    $submission = YamlFormSubmission::load($sid);

    /** @var \Drupal\file\Entity\File $test_file_0 */
    $test_file_0_fid = $this->getLastFileId();
    $test_file_0 = File::load($test_file_0_fid);

    // Check that test file 0 was uploaded to the current submission.
    $this->assertEqual($submission->getData('file'), $test_file_0_fid, 'Test file 0 was upload to the current submission');

    // Check test file 0 file usage.
    $this->assertIdentical(['yamlform' => ['yamlform_submission' => [1 => $sid]]], $file_usage->listUsage($test_file_0), 'The file has 1 usage.');

    // Check test file 0 uploaded file path.
    $this->assertEqual($test_file_0->getFileUri(), 'public://yamlform/test_inputs_managed_file/' . $sid . '/' . $test_files[0]->filename);

    // Check that test file 0 exists.
    $this->assert(file_exists($test_file_0->getFileUri()), 'File exists');

    $this->drupalLogin($this->adminSubmissionUser);

    // Remove the uploaded file.
    $this->drupalPostForm('/admin/structure/yamlform/results/manage/' . $sid . '/edit', [], t('Remove'));

    // Upload new file.
    $edit = [
      'files[file]' => \Drupal::service('file_system')->realpath($test_files[1]->uri),
    ];
    $this->drupalPostForm(NULL, $edit, t('Upload'));

    // Submit the new file.
    $this->drupalPostForm(NULL, [], t('Submit'));

    /** @var \Drupal\file\Entity\File $test_file_0 */
    $test_file_1_fid = $this->getLastFileId();
    $test_file_1 = File::load($test_file_1_fid);

    \Drupal::entityManager()->getStorage('yamlform_submission')->resetCache();
    $submission = YamlFormSubmission::load($sid);

    // Check that test file 1 was uploaded to the current submission.
    $this->assertEqual($submission->getData('file'), $test_file_1_fid, 'Test file 1 was upload to the current submission');

    // Check that test file 0 was deleted from the disk and database.
    $this->assert(!file_exists($test_file_0->getFileUri()), 'Test file 0 deleted from disk');
    $this->assertEqual(0, db_query('SELECT COUNT(fid) AS total FROM {file_managed} WHERE fid=:fid', [':fid' => $test_file_0_fid])->fetchField(), 'Test file 0 deleted from database');
    $this->assertEqual(0, db_query('SELECT COUNT(fid) AS total FROM {file_usage} WHERE fid=:fid', [':fid' => $test_file_0_fid])->fetchField(), 'Test file 0 deleted from database');

    // Check test file 1 file usage.
    $this->assertIdentical(['yamlform' => ['yamlform_submission' => [1 => $sid]]], $file_usage->listUsage($test_file_1), 'The file has 1 usage.');

    // Delete the submission.
    $submission->delete();

    // Check that test file 1 was deleted from the disk and database.
    $this->assert(!file_exists($test_file_1->getFileUri()), 'Test file 1 deleted from disk');
    $this->assertEqual(0, db_query('SELECT COUNT(fid) AS total FROM {file_managed} WHERE fid=:fid', [':fid' => $test_file_1_fid])->fetchField(), 'Test file 1 deleted from database');
  }

  /****************************************************************************/
  // Helper functions. From: \Drupal\file\Tests\FileFieldTestBase::getTestFile
  /****************************************************************************/

  /**
   * Retrieves the fid of the last inserted file.
   */
  protected function getLastFileId() {
    return (int) db_query('SELECT MAX(fid) FROM {file_managed}')->fetchField();
  }

}
