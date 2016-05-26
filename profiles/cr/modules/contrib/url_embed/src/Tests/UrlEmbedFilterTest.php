<?php

/**
 * @file
 * Contains \Drupal\url_embed\Tests\UrlEmbedFilterTest.
 */

namespace Drupal\url_embed\Tests;

/**
 * Tests the url_embed filter.
 *
 * @group url_embed
 */
class UrlEmbedFilterTest extends UrlEmbedTestBase {

  /**
   * Tests the url_embed filter.
   *
   * Ensures that iframes are getting rendered when valid urls
   * are passed. Also tests situations when embed fails.
   */
  public function testFilter() {
    // Tests url embed using sample flickr url.
    $content = '<drupal-url data-embed-url="' . static::FLICKR_URL . '">This placeholder should not be rendered.</drupal-url>';
    $settings = array();
    $settings['type'] = 'page';
    $settings['title'] = 'Test url embed with sample flickr url';
    $settings['body'] = array(array('value' => $content, 'format' => 'custom_format'));
    $node = $this->drupalCreateNode($settings);
    $this->drupalGet('node/' . $node->id());
    $this->assertRaw(static::FLICKR_OUTPUT);
    $this->assertNoText(strip_tags($content), 'Placeholder does not appear in the output when embed is successful.');

    // Ensure that placeholder is not replaced when embed is unsuccessful.
    $content = '<drupal-url data-embed-url="">This placeholder should be rendered since specified URL does not exists.</drupal-url>';
    $settings = array();
    $settings['type'] = 'page';
    $settings['title'] = 'Test that placeholder is retained when specified URL does not exists';
    $settings['body'] = array(array('value' => $content, 'format' => 'custom_format'));
    $node = $this->drupalCreateNode($settings);
    $this->drupalGet('node/' . $node->id());
    $this->assertNoText(strip_tags($content), 'Placeholder does not appear in the output when embed is unsuccessful.');

    // Test that tag of container element is not replaced when it's not
    // <drupal-url>.
    $content = '<not-drupal-url data-embed-url="' . static::FLICKR_URL . '" data-url-provider="Flickr">this placeholder should not be rendered.</not-drupal-url>';
    $settings = array();
    $settings['type'] = 'page';
    $settings['title'] = 'test url embed with embed-url';
    $settings['body'] = array(array('value' => $content, 'format' => 'custom_format'));
    $node = $this->drupalCreateNode($settings);
    $this->drupalget('node/' . $node->id());
    $this->assertRaw('</not-drupal-url>');
    $content = '<div data-embed-url="' . static::FLICKR_URL . '">this placeholder should not be rendered.</div>';
    $settings = array();
    $settings['type'] = 'page';
    $settings['title'] = 'test url embed with embed-url';
    $settings['body'] = array(array('value' => $content, 'format' => 'custom_format'));
    $node = $this->drupalCreateNode($settings);
    $this->drupalget('node/' . $node->id());
    $this->assertRaw('<div data-embed-url="' . static::FLICKR_URL . '"');
  }

}
