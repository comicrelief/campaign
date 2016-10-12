<?php

namespace Drupal\yamlform\Tests;

/**
 * Tests for form submission access.
 *
 * @group YamlForm
 */
class YamlFormSubmissionAccessTest extends YamlFormTestBase {

  /**
   * Tests form submission access.
   */
  public function testAccess() {
    /** @var \Drupal\yamlform\YamlFormInterface $yamlform */
    /** @var \Drupal\yamlform\YamlFormSubmissionInterface[] $submissions */
    list($yamlform, $submissions) = $this->createYamlFormWithSubmissions();

    $yamlform_id = $yamlform->id();
    $sid = $submissions[0]->id();

    // Check all results access denied.
    $this->drupalGet('/admin/structure/yamlform/results/manage');
    $this->assertResponse(403);

    // Check form results access denied.
    $this->drupalGet("/admin/structure/yamlform/manage/$yamlform_id/results/submissions");
    $this->assertResponse(403);

    // Check form submission access denied.
    $this->drupalGet("/admin/structure/yamlform/manage/$yamlform_id/submission/$sid");
    $this->assertResponse(403);

    $viewSubmissionUser = $this->drupalCreateUser([
      'access content',
      'access yamlform overview',
      'view any yamlform submission',
    ]);
    $this->drupalLogin($viewSubmissionUser);

    // Check all results access allowed.
    $this->drupalGet('/admin/structure/yamlform/results/manage');
    $this->assertResponse(200);

    // Check form results access allowed.
    $this->drupalGet("/admin/structure/yamlform/manage/$yamlform_id/results/submissions");
    $this->assertResponse(200);

    // Check form submission access allowed.
    $this->drupalGet("/admin/structure/yamlform/manage/$yamlform_id/submission/$sid");
    $this->assertResponse(200);
  }

}
