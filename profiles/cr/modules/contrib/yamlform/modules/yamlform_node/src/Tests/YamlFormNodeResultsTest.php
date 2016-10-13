<?php

namespace Drupal\yamlform_node\Tests;

use Drupal\Core\Url;
use Drupal\yamlform\Entity\YamlForm;
use Drupal\yamlform\Tests\YamlFormTestBase;

/**
 * Tests for form node results.
 *
 * @group YamlFormNode
 */
class YamlFormNodeResultsTest extends YamlFormTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['system', 'block', 'node', 'user', 'yamlform', 'yamlform_test', 'yamlform_node'];

  /**
   * Tests form node results.
   */
  public function testResults() {
    /** @var \Drupal\yamlform\YamlFormSubmissionStorageInterface $submission_storage */
    $submission_storage = \Drupal::entityTypeManager()->getStorage('yamlform_submission');

    $this->createUsers();

    $yamlform = YamlForm::load('contact');

    // Create node.
    $node = $this->drupalCreateNode(['type' => 'yamlform']);

    /* Form entity reference */

    // Check access denied to form results.
    $this->drupalLogin($this->adminSubmissionUser);
    $this->drupalGet('node/' . $node->id() . '/yamlform/results/submissions');
    $this->assertResponse(403);

    // Set Node form to the contact form.
    $node->yamlform->target_id = 'contact';
    $node->yamlform->status = 1;
    $node->save();

    /* Submission management */

    // Generate 3 node submissions and 3 yamlform submissions.
    $this->drupalLogin($this->normalUser);
    $node_sids = [];
    $yamlform_sids = [];
    for ($i = 1; $i <= 3; $i++) {
      $edit = [
        'name' => "node$i",
        'email' => "node$i@example.com",
        'subject' => "Node $i subject",
        'message' => "Node $i message",
      ];
      $this->drupalPostForm('node/' . $node->id(), $edit, t('Send message'));
      $node_sids[$i] = $this->getLastSubmissionId($yamlform);
      $edit = [
        'name' => "yamlform$i",
        'email' => "yamlform$i@example.com",
        'subject' => "Form $i subject",
        'message' => "Form $i message",
      ];
      $this->drupalPostForm('yamlform/contact', $edit, t('Send message'));
      $yamlform_sids[$i] = $this->getLastSubmissionId($yamlform);
    }

    // Check that 6 submission were created.
    $this->assertEqual($submission_storage->getTotal($yamlform, $node), 3);
    $this->assertEqual($submission_storage->getTotal($yamlform), 6);

    // Check form node results.
    $this->drupalLogin($this->adminSubmissionUser);
    $node_route_parameters = ['node' => $node->id(), 'yamlform_submission' => $node_sids[1]];
    $node_submission_url = Url::fromRoute('entity.node.yamlform_submission.canonical', $node_route_parameters);
    $yamlform_submission_route_parameters = ['yamlform' => 'contact', 'yamlform_submission' => $node_sids[1]];
    $yamlform_submission_url = Url::fromRoute('entity.yamlform_submission.canonical', $yamlform_submission_route_parameters);

    $this->drupalGet('node/' . $node->id() . '/yamlform/results/submissions');
    $this->assertResponse(200);
    $this->assertRaw('<h1 class="page-title">' . $node->label() . '</h1>');
    $this->assertNoRaw('<h1 class="page-title">' . $yamlform->label() . '</h1>');
    $this->assertRaw(('<a href="' . $node_submission_url->toString() . '">' . $node_sids[1] . '</a>'));
    $this->assertNoRaw(('<a href="' . $yamlform_submission_url->toString() . '">' . $yamlform_sids[1] . '</a>'));

    // Check form node title.
    $this->drupalGet('node/' . $node->id() . '/yamlform/submission/' . $node_sids[1]);
    $this->assertRaw($node->label() . ': Submission #' . $node_sids[1]);
    $this->drupalGet('node/' . $node->id() . '/yamlform/submission/' . $node_sids[2]);
    $this->assertRaw($node->label() . ': Submission #' . $node_sids[2]);

    // Check form node navigation.
    $this->drupalGet('node/' . $node->id() . '/yamlform/submission/' . $node_sids[1]);
    $node_route_parameters = ['node' => $node->id(), 'yamlform_submission' => $node_sids[2]];
    $node_submission_url = Url::fromRoute('entity.node.yamlform_submission.canonical', $node_route_parameters);
    $this->assertRaw('<a href="' . $node_submission_url->toString() . '" rel="next" title="Go to next page">Next submission <b>›</b></a>');

    // Check form node saved draft.
    $yamlform->setSetting('draft', TRUE);
    $yamlform->save();

    // Check form saved draft.
    $this->drupalLogin($this->normalUser);
    $edit = [
      'name' => "nodeDraft",
      'email' => "nodeDraft@example.com",
      'subject' => "Node draft subject",
      'message' => "Node draft message",
    ];
    $this->drupalPostForm('node/' . $node->id(), $edit, t('Save Draft'));
    $this->drupalGet('node/' . $node->id());
    $this->assertRaw('A partially-completed form was found. Please complete the remaining portions.');
    $this->drupalGet('yamlform/contact');
    $this->assertNoRaw('A partially-completed form was found. Please complete the remaining portions.');

    /* Table customization */
    $this->drupalLogin($this->adminSubmissionUser);

    // Check default node results table.
    $this->drupalGet('node/' . $node->id() . '/yamlform/results/table');
    $this->assertRaw('<th specifier="serial" aria-sort="descending" class="is-active">');
    $this->assertRaw('sort by Created');
    $this->assertNoRaw('sort by Changed');

    // Customize to main form's results table.
    $edit = [
      'columns[created][checkbox]' => FALSE,
      'columns[changed][checkbox]' => TRUE,
      'direction' => 'asc',
      'limit' => 20,
      'default' => TRUE,
    ];
    $this->drupalPostForm('admin/structure/yamlform/manage/' . $yamlform->id() . '/results/table/custom', $edit, t('Save'));
    $this->assertRaw('The customized table has been saved.');

    // Check that the form node's results table is now customized.
    $this->drupalGet('node/' . $node->id() . '/yamlform/results/table');
    $this->assertRaw('<th specifier="serial" aria-sort="ascending" class="is-active">');
    $this->assertNoRaw('sort by Created');
    $this->assertRaw('sort by Changed');

    $this->drupalLogout();

    /* Access control */

    // Create any and own user accounts.
    $any_user = $this->drupalCreateUser([
      'access content',
      'view yamlform submissions any node',
      'edit yamlform submissions any node',
      'delete yamlform submissions any node',
    ]);
    $own_user = $this->drupalCreateUser([
      'access content',
      'view yamlform submissions own node',
      'edit yamlform submissions own node',
      'delete yamlform submissions own node',
    ]);

    // Check accessing results posted to any form node.
    $this->drupalLogin($any_user);
    $this->drupalGet('node/' . $node->id() . '/yamlform/results/submissions');
    $this->assertResponse(200);

    // Check accessing results posted to own form node.
    $this->drupalLogin($own_user);
    $this->drupalGet('node/' . $node->id() . '/yamlform/results/submissions');
    $this->assertResponse(403);

    $node->setOwnerId($own_user->id())->save();
    $this->drupalGet('node/' . $node->id() . '/yamlform/results/submissions');
    $this->assertResponse(200);

    // Check deleting form node results.
    $this->drupalPostForm('node/' . $node->id() . '/yamlform/results/clear', [], t('Clear'));
    $this->assertEqual($submission_storage->getTotal($yamlform, $node), 0);
    $this->assertEqual($submission_storage->getTotal($yamlform), 3);
  }

}
