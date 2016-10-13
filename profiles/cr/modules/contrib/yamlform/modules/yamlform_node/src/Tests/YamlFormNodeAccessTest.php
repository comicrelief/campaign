<?php

namespace Drupal\yamlform_node\Tests;

use Drupal\yamlform\Entity\YamlForm;
use Drupal\yamlform\Tests\YamlFormTestBase;

/**
 * Tests for form node access rules.
 *
 * @group YamlFormNode
 */
class YamlFormNodeAccessTest extends YamlFormTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['system', 'block', 'node', 'user', 'yamlform', 'yamlform_test', 'yamlform_node'];

  /**
   * Tests form node access rules.
   *
   * @see \Drupal\yamlform\Tests\YamlFormAccessTest::testAccessRules
   */
  public function testAccessRules() {
    // Create form node that references the contact form.
    $yamlform = YamlForm::load('contact');
    $node = $this->drupalCreateNode(['type' => 'yamlform']);
    $node->yamlform->target_id = 'contact';
    $node->yamlform->status = 1;
    $node->save();
    $nid = $node->id();

    // Log in normal user and get their rid.
    $this->drupalLogin($this->normalUser);
    $roles = $this->normalUser->getRoles(TRUE);
    $rid = reset($roles);
    $uid = $this->normalUser->id();

    // Add one submission to the YAML Form node.
    $edit = [
      'name' => '{name}',
      'email' => 'example@example.com',
      'subject' => '{subject}',
      'message' => '{message',
    ];
    $this->drupalPostForm('node/' . $node->id(), $edit, t('Send message'));
    $sid = $this->getLastSubmissionId($yamlform);

    // Check create authenticated/anonymous access.
    $yamlform->setAccessRules(YamlForm::getDefaultAccessRules())->save();
    $this->drupalGet('node/' . $node->id());
    $this->assertFieldByName('name', $this->normalUser->getAccountName());
    $this->assertFieldByName('email', $this->normalUser->getEmail());

    $access_rules = [
      'create' => [
        'roles' => [],
        'users' => [],
      ],
    ] + YamlForm::getDefaultAccessRules();
    $yamlform->setAccessRules($access_rules)->save();

    // Check no access.
    $this->drupalGet('node/' . $node->id());
    $this->assertNoFieldByName('name', $this->normalUser->getAccountName());
    $this->assertNoFieldByName('email', $this->normalUser->getEmail());

    $any_tests = [
      'node/{node}/yamlform/results/submissions' => 'view_any',
      'node/{node}/yamlform/results/table' => 'view_any',
      'node/{node}/yamlform/results/download' => 'view_any',
      'node/{node}/yamlform/results/clear' => 'purge_any',
      'node/{node}/yamlform/submission/{yamlform_submission}' => 'view_any',
      'node/{node}/yamlform/submission/{yamlform_submission}/text' => 'view_any',
      'node/{node}/yamlform/submission/{yamlform_submission}/yaml' => 'view_any',
      'node/{node}/yamlform/submission/{yamlform_submission}/edit' => 'update_any',
      'node/{node}/yamlform/submission/{yamlform_submission}/delete' => 'delete_any',
    ];

    // Check that all the test paths are access denied for authenticated.
    foreach ($any_tests as $path => $permission) {
      $path = str_replace('{node}', $nid, $path);
      $path = str_replace('{yamlform_submission}', $sid, $path);

      $this->drupalGet($path);
      $this->assertResponse(403, 'Form returns access denied');
    }

    // Check access rules by role and user id.
    foreach ($any_tests as $path => $permission) {
      $path = str_replace('{node}', $nid, $path);
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

      // Check access rule via role.
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
  }

}
