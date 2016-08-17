<?php

namespace Drupal\Tests\media_entity_browser\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\JavascriptTestBase;
use Drupal\media_entity\Entity\Media;
use Drupal\media_entity\Entity\MediaBundle;

/**
 * A test for the media entity browser.
 *
 * @group media_entity_browser
 */
class MediaEntityBrowserTest extends JavascriptTestBase {

  /**
   * Modules to install.
   *
   * @var array
   */
  public static $modules = [
    'media_entity',
    'entity_browser',
    'media_entity_browser',
    'video_embed_media',
    'ctools',
    // @todo, fix after https://www.drupal.org/node/2746203.
    'block',
  ];

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->drupalLogin($this->drupalCreateUser(array_keys($this->container->get('user.permissions')->getPermissions())));
    MediaBundle::create([
      'label' => 'Video',
      'id' => 'video',
      'description' => 'Video bundle.',
      'type' => 'video_embed_field',
    ])->save();
    Media::create([
      'bundle' => 'video',
      'field_media_video_embed_field' => [['value' => 'https://www.youtube.com/watch?v=XgYu7-DQjDQ']],
    ])->save();
  }

  /**
   * Test the media entity browser.
   */
  public function testMediaBrowser() {
    $this->drupalGet('entity-browser/iframe/media_entity_browser');

    $this->assertSession()->elementExists('css', '.view-media-entity-browser');
    $this->assertSession()->elementExists('css', '.image-style-media-entity-browser-thumbnail');

    $this->assertSession()->elementNotExists('css', '.views-row.checked');
    $this->getSession()->getPage()->find('css', '.views-row')->press();
    $this->assertSession()->elementExists('css', '.views-row.checked');

    $this->assertSession()->buttonExists('Select Media');
  }

}
