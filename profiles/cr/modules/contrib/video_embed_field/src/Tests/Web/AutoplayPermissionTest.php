<?php

/**
 * @file
 * Contains \Drupal\video_embed_field\Tests\Web\AutoplayPermissionTest.
 */

namespace Drupal\video_embed_field\Tests\Web;

use Drupal\video_embed_field\Tests\WebTestBase;

/**
 * Test the autoplay permission works.
 *
 * @group video_embed_field
 */
class AutoplayPermissionTest extends WebTestBase {

  /**
   * Test the autoplay permission works.
   */
  function testVideoEmbedFieldDefaultWidget() {
    $node = $this->drupalCreateNode([
      'type' => $this->contentTypeName,
      $this->fieldName => [
        ['value' => 'https://vimeo.com/80896303']
      ],
    ]);
    $this->entityDisplay->setComponent($this->fieldName, [
      'type' => 'video_embed_field_video',
      'settings' => [
        'autoplay' => TRUE,
      ],
    ])->save();
    $bypass_autoplay_user = $this->createUser(['never autoplay videos']);
    // Assert a user with the permission doesn't get autoplay.
    $this->drupalLogin($bypass_autoplay_user);
    $this->drupalGet('node/' . $node->id());
    $this->assertRaw('autoplay=0');
    // Ensure an anonymous user gets autoplay.
    $this->drupalLogout();
    $this->drupalGet('node/' . $node->id());
    $this->assertRaw('autoplay=1');
  }

}
