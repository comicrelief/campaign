<?php

namespace Drupal\Tests\video_embed_field\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\JavascriptTestBase;
use Drupal\Tests\video_embed_field\Functional\EntityDisplaySetupTrait;

/**
 * Test the colorbox formatter.
 *
 * @group video_embed_field
 */
class ColorboxFormatterTest extends JavascriptTestBase {

  use EntityDisplaySetupTrait;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'node',
    'colorbox',
    'video_embed_field',
    'video_embed_field_colorbox_test',
  ];

  /**
   * How long it takes for colorbox to open.
   */
  const COLORBOX_LAUNCH_TIME = 500;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->setupEntityDisplays();
  }

  /**
   * Test the colorbox formatter.
   */
  public function testColorboxFormatter() {
    $this->setDisplayComponentSettings('video_embed_field_colorbox', [
      'autoplay' => FALSE,
      'responsive' => TRUE,
    ]);
    $node = $this->createVideoNode('https://www.youtube.com/watch?v=fdbFVWupSsw');
    $this->drupalGet('node/' . $node->id());
    $this->getSession()->wait(50);
    $this->click('img');
    $this->getSession()->wait(static::COLORBOX_LAUNCH_TIME);
    $this->assertSession()->elementExists('css', '#colorbox .video-embed-field-responsive-video');
    // Make sure the right library files are loaded on the page.
    $this->assertSession()->elementContains('css', 'style', 'colorbox/styles/default/colorbox_style.css');
    $this->assertSession()->elementContains('css', 'style', 'video_embed_field/css/video_embed_field.responsive-video.css');
  }

}
