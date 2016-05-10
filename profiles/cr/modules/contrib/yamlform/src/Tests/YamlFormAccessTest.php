<?php

/**
 * @file
 * Definition of Drupal\yamlform\test\YamlFormAccessTest.
 */

namespace Drupal\yamlform\Tests;
use Drupal\Component\Render\FormattableMarkup;
use Drupal\yamlform\Entity\YamlForm;

/**
 * Tests for YAML form access rules.
 *
 * @group YamlForm
 */
class YamlFormAccessTest extends YamlFormTestBase {

  /**
   * Tests YAML form access rules.
   */
  public function testAccessRules() {
    /** @var \Drupal\yamlform\YamlFormInterface $yamlform */
    /** @var \Drupal\yamlform\Entity\YamlFormSubmission[] $submissions */
    list($yamlform, $submissions) = $this->createYamlFormWithSubmissions();

    $account = $this->drupalCreateUser(['access content']);

    $yamlform_id = $yamlform->id();
    $sid = $submissions[0]->id();
    $uid = $account->id();
    $rid = $account->getRoles()[1];

    // Check create authenticated/anonymous access.
    $yamlform->setAccessRules(YamlForm::getDefaultAccessRules())->save();
    $this->drupalGet('yamlform/' . $yamlform->id());
    $this->assertResponse(200, 'YAML form create submission access for anonymous/authenticated user.');

    $access_rules = [
      'create' => [
        'roles' => [],
        'users' => [],
      ],
    ] + YamlForm::getDefaultAccessRules();
    $yamlform->setAccessRules($access_rules)->save();

    // Check no access.
    $this->drupalGet('yamlform/' . $yamlform->id());
    $this->assertResponse(403, 'YAML form returns access denied');

    $any_tests = [
      'yamlform/{yamlform}' => 'create',
      'admin/structure/yamlform/manage/{yamlform}/results/submissions' => 'view_any',
      'admin/structure/yamlform/manage/{yamlform}/results/table' => 'view_any',
      'admin/structure/yamlform/manage/{yamlform}/results/download' => 'view_any',
      'admin/structure/yamlform/manage/{yamlform}/results/clear' => 'purge_any',
      'admin/structure/yamlform/results/manage/{yamlform_submission}' => 'view_any',
      'admin/structure/yamlform/results/manage/{yamlform_submission}/text' => 'view_any',
      'admin/structure/yamlform/results/manage/{yamlform_submission}/yaml' => 'view_any',
      'admin/structure/yamlform/results/manage/{yamlform_submission}/edit' => 'update_any',
      'admin/structure/yamlform/results/manage/{yamlform_submission}/delete' => 'delete_any',
    ];

    // Check that all the test paths are access denied for anonymous users.
    foreach ($any_tests as $path => $permission) {
      $path = str_replace('{yamlform}', $yamlform_id, $path);
      $path = str_replace('{yamlform_submission}', $sid, $path);

      $this->drupalGet($path);
      $this->assertResponse(403, 'YAML form returns access denied');
    }

    $this->drupalLogin($account);

    // Check that all the test paths are access denied for anonymous.
    foreach ($any_tests as $path => $permission) {
      $path = str_replace('{yamlform}', $yamlform_id, $path);
      $path = str_replace('{yamlform_submission}', $sid, $path);

      $this->drupalGet($path);
      $this->assertResponse(403, 'YAML form returns access denied');
    }

    // Check access rules by role and user id.
    foreach ($any_tests as $path => $permission) {
      $path = str_replace('{yamlform}', $yamlform_id, $path);
      $path = str_replace('{yamlform_submission}', $sid, $path);

      // Check access rule via role.
      $access_rules = [
        $permission => [
          'roles' => [$rid],
          'users' => [],
        ],
      ] + YamlForm::getDefaultAccessRules();
      $yamlform->setAccessRules($access_rules)->save();
      $this->drupalGet($path);
      $this->assertResponse(200, 'YAML form allows access via role access rules');

      // Check access rule via role.
      $access_rules = [
        $permission => [
          'roles' => [],
          'users' => [$uid],
        ],
      ] + YamlForm::getDefaultAccessRules();
      $yamlform->setAccessRules($access_rules)->save();
      $this->drupalGet($path);
      $this->assertResponse(200, 'YAML form allows access via user access rules');
    }

    // Check own / user specific access rules.
    $access_rules = [
      'view_own' => [
        'roles' => [$rid],
        'users' => [],
      ],
      'update_own' => [
        'roles' => [$rid],
        'users' => [],
      ],
      'delete_own' => [
        'roles' => [$rid],
        'users' => [],
      ],
    ] + YamlForm::getDefaultAccessRules();
    $yamlform->setAccessRules($access_rules)->save();

    // Login and post a submission as a user.
    $this->drupalLogin($account);

    // Check no view previous submission message.
    $this->drupalGet('yamlform/' . $yamlform->id());
    $this->assertNoRaw('You have already submitted this form.');
    $this->assertNoRaw('View your previous submissions');

    $sid = $this->postSubmission($yamlform);

    // Check view previous submission message.
    $this->drupalGet('yamlform/' . $yamlform->id());
    $this->assertRaw('You have already submitted this form.');
    $this->assertRaw('View your previous submissions');

    // Check the new submission's view, update, and delete access for the user.
    $test_own = [
      'admin/structure/yamlform/manage/{yamlform}/results/submissions' => 403,
      'admin/structure/yamlform/manage/{yamlform}/results/table' => 403,
      'admin/structure/yamlform/manage/{yamlform}/results/download' => 403,
      'admin/structure/yamlform/manage/{yamlform}/results/clear' => 403,
      'admin/structure/yamlform/results/manage/{yamlform_submission}' => 200,
      'admin/structure/yamlform/results/manage/{yamlform_submission}/text' => 403,
      'admin/structure/yamlform/results/manage/{yamlform_submission}/yaml' => 403,
      'admin/structure/yamlform/results/manage/{yamlform_submission}/edit' => 200,
      'admin/structure/yamlform/results/manage/{yamlform_submission}/delete' => 200,
    ];
    foreach ($test_own as $path => $status_code) {
      $path = str_replace('{yamlform}', $yamlform_id, $path);
      $path = str_replace('{yamlform_submission}', $sid, $path);

      $this->drupalGet($path);
      $this->assertResponse($status_code, new FormattableMarkup('YAML form @status_code access via own access rules.', ['@status_code' => ($status_code == 403 ? 'denies' : 'allows')]));
    }
  }

}
