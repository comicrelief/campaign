<?php

namespace Drupal\yamlform\Tests;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\yamlform\Entity\YamlForm;

/**
 * Tests for form access rules.
 *
 * @group YamlForm
 */
class YamlFormAccessTest extends YamlFormTestBase {

  /**
   * Tests form access rules.
   */
  public function testAccessControlHandler() {
    // Login as user who can access own form.
    $this->drupalLogin($this->ownFormUser);

    // Check create own form.
    $this->drupalPostForm('admin/structure/yamlform/add', ['id' => 'test_own', 'title' => 'test_own', 'elements' => "test:\n  '#markup': 'test'"], t('Save'));

    // Check duplicate own form.
    $this->drupalGet('admin/structure/yamlform/manage/test_own/duplicate');
    $this->assertResponse(200);

    // Check delete own form.
    $this->drupalGet('admin/structure/yamlform/manage/test_own/delete');
    $this->assertResponse(200);

    // Check access own form submissions.
    $this->drupalGet('admin/structure/yamlform/manage/test_own/results/submissions');
    $this->assertResponse(200);

    // Login as user who can access any form.
    $this->drupalLogin($this->anyFormUser);

    // Check duplicate any form.
    $this->drupalGet('admin/structure/yamlform/manage/test_own/duplicate');
    $this->assertResponse(200);

    // Check delete any form.
    $this->drupalGet('admin/structure/yamlform/manage/test_own/delete');
    $this->assertResponse(200);

    // Check access any form submissions.
    $this->drupalGet('admin/structure/yamlform/manage/test_own/results/submissions');
    $this->assertResponse(200);

    // Change the owner of the form to 'any' user.
    $own_yamlform = YamlForm::load('test_own');
    $own_yamlform->setOwner($this->anyFormUser)->save();

    // Login as user who can access own form.
    $this->drupalLogin($this->ownFormUser);

    // Check duplicate denied any form.
    $this->drupalGet('admin/structure/yamlform/manage/test_own/duplicate');
    $this->assertResponse(403);

    // Check delete denied any form.
    $this->drupalGet('admin/structure/yamlform/manage/test_own/delete');
    $this->assertResponse(403);

    // Check access denied any form submissions.
    $this->drupalGet('admin/structure/yamlform/manage/test_own/results/submissions');
    $this->assertResponse(403);
  }

  /**
   * Tests form access rules.
   */
  public function testAccessRules() {
    global $base_path;

    /** @var \Drupal\yamlform\YamlFormInterface $yamlform */
    /** @var \Drupal\yamlform\YamlFormSubmissionInterface[] $submissions */
    list($yamlform, $submissions) = $this->createYamlFormWithSubmissions();
    $account = $this->drupalCreateUser(['access content']);

    $yamlform_id = $yamlform->id();
    $sid = $submissions[0]->id();
    $uid = $account->id();
    $rid = $account->getRoles()[1];

    // Check create authenticated/anonymous access.
    $yamlform->setAccessRules(YamlForm::getDefaultAccessRules())->save();
    $this->drupalGet('yamlform/' . $yamlform->id());
    $this->assertResponse(200, 'Form create submission access for anonymous/authenticated user.');

    $access_rules = [
      'create' => [
        'roles' => [],
        'users' => [],
      ],
    ] + YamlForm::getDefaultAccessRules();
    $yamlform->setAccessRules($access_rules)->save();

    // Check no access.
    $this->drupalGet('yamlform/' . $yamlform->id());
    $this->assertResponse(403, 'Form returns access denied');

    $any_tests = [
      'yamlform/{yamlform}' => 'create',
      'admin/structure/yamlform/manage/{yamlform}/results/submissions' => 'view_any',
      'admin/structure/yamlform/manage/{yamlform}/results/table' => 'view_any',
      'admin/structure/yamlform/manage/{yamlform}/results/download' => 'view_any',
      'admin/structure/yamlform/manage/{yamlform}/results/clear' => 'purge_any',
      'admin/structure/yamlform/manage/{yamlform}/submission/{yamlform_submission}' => 'view_any',
      'admin/structure/yamlform/manage/{yamlform}/submission/{yamlform_submission}/text' => 'view_any',
      'admin/structure/yamlform/manage/{yamlform}/submission/{yamlform_submission}/yaml' => 'view_any',
      'admin/structure/yamlform/manage/{yamlform}/submission/{yamlform_submission}/edit' => 'update_any',
      'admin/structure/yamlform/manage/{yamlform}/submission/{yamlform_submission}/delete' => 'delete_any',
    ];

    // Check that all the test paths are access denied for anonymous users.
    foreach ($any_tests as $path => $permission) {
      $path = str_replace('{yamlform}', $yamlform_id, $path);
      $path = str_replace('{yamlform_submission}', $sid, $path);

      $this->drupalGet($path);
      $this->assertResponse(403, 'Form returns access denied');
    }

    $this->drupalLogin($account);

    // Check that all the test paths are access denied for authenticated.
    foreach ($any_tests as $path => $permission) {
      $path = str_replace('{yamlform}', $yamlform_id, $path);
      $path = str_replace('{yamlform_submission}', $sid, $path);

      $this->drupalGet($path);
      $this->assertResponse(403, 'Form returns access denied');
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
      $this->assertResponse(200, 'Form allows access via role access rules');

      // Check access rule via user id.
      $access_rules = [
        $permission => [
          'roles' => [],
          'users' => [$uid],
        ],
      ] + YamlForm::getDefaultAccessRules();
      $yamlform->setAccessRules($access_rules)->save();
      $this->drupalGet($path);
      $this->assertResponse(200, 'Form allows access via user access rules');
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
    $this->assertNoRaw('View your previous submission');

    $sid = $this->postSubmission($yamlform);

    // Check view previous submission message.
    $this->drupalGet('yamlform/' . $yamlform->id());
    $this->assertRaw('You have already submitted this form.');
    $this->assertRaw("<a href=\"{$base_path}yamlform/{$yamlform_id}/submissions/{$sid}\">View your previous submission</a>.");

    $sid = $this->postSubmission($yamlform);

    // Check view previous submissions message.
    $this->drupalGet('yamlform/' . $yamlform->id());
    $this->assertRaw('You have already submitted this form.');
    $this->assertRaw("<a href=\"{$base_path}yamlform/{$yamlform_id}/submissions\">View your previous submissions</a>");

    // Check the new submission's view, update, and delete access for the user.
    $test_own = [
      'admin/structure/yamlform/manage/{yamlform}/results/submissions' => 403,
      'admin/structure/yamlform/manage/{yamlform}/results/table' => 403,
      'admin/structure/yamlform/manage/{yamlform}/results/download' => 403,
      'admin/structure/yamlform/manage/{yamlform}/results/clear' => 403,
      'admin/structure/yamlform/manage/{yamlform}/submission/{yamlform_submission}' => 200,
      'admin/structure/yamlform/manage/{yamlform}/submission/{yamlform_submission}/text' => 403,
      'admin/structure/yamlform/manage/{yamlform}/submission/{yamlform_submission}/yaml' => 403,
      'admin/structure/yamlform/manage/{yamlform}/submission/{yamlform_submission}/edit' => 200,
      'admin/structure/yamlform/manage/{yamlform}/submission/{yamlform_submission}/delete' => 200,
    ];
    foreach ($test_own as $path => $status_code) {
      $path = str_replace('{yamlform}', $yamlform_id, $path);
      $path = str_replace('{yamlform_submission}', $sid, $path);

      $this->drupalGet($path);
      $this->assertResponse($status_code, new FormattableMarkup('Form @status_code access via own access rules.', ['@status_code' => ($status_code == 403 ? 'denies' : 'allows')]));
    }
  }

}
