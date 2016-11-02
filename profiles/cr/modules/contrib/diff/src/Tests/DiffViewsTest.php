<?php

namespace Drupal\diff\Tests;

use Drupal\Core\Url;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use Drupal\views\Tests\ViewTestBase;

/**
 * Tests the diff views integration.
 *
 * @group diff
 */
class DiffViewsTest extends ViewTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = ['node', 'diff', 'user', 'diff_test'];

  /**
   * Tests the behavior of a view that uses the diff_from and diff_to fields.
   */
  public function testDiffView() {
    $node_type = NodeType::create([
      'type' => 'article',
      'label' => 'Article',
    ]);
    $node_type->save();
    $node = Node::create([
      'type' => 'article',
      'title' => 'Test article: giraffe',
    ]);
    $node->save();

    $node->setNewRevision(TRUE);
    $node->setTitle('Test article: llama');
    $node->save();

    $this->drupalGet("node/{$node->id()}/diff-views");
    $this->assertResponse(403);

    $user = $this->createUser(['view all revisions']);
    $this->drupalLogin($user);

    $this->drupalGet("node/{$node->id()}/diff-views");
    $this->assertResponse(200);

    $from_first = (string) $this->cssSelect('#edit-diff-from--3')[0]->attributes()['value'];
    $to_second = (string) $this->cssSelect('#edit-diff-to--2')[0]->attributes()['value'];

    $edit = [
      'diff_from' => $from_first,
      'diff_to' => $to_second,
    ];
    $this->drupalPostForm(NULL, $edit, t('Compare'));
    $expected_url = Url::fromRoute(
      'diff.revisions_diff',
      // Route parameters
      [
        'node' => $node->id(),
        'left_revision' => 1,
        'right_revision' => 2,
        'filter' => 'split_fields',
      ],
      // Additional route options
      [
        'query' => [
          'destination' => Url::fromUri('internal:/node/1/diff-views')->toString(),
        ],
      ]
    );
    $this->assertUrl($expected_url);
    $this->assertRaw('<td class="diff-context diff-deletedline">Test article: <span class="diffchange">giraffe</span></td>');
    $this->assertRaw('<td class="diff-context diff-addedline">Test article: <span class="diffchange">llama</span></td>');
  }

}
