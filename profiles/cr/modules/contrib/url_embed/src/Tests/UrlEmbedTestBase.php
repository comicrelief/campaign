<?php

/**
 * @file
 * Contains \Drupal\url_embed\Tests\UrlEmbedTestBase.
 */

namespace Drupal\url_embed\Tests;

use Drupal\editor\Entity\Editor;
use Drupal\filter\Entity\FilterFormat;
use Drupal\simpletest\WebTestBase;

/**
 * Base class for all url_embed tests.
 */
abstract class UrlEmbedTestBase extends WebTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('url_embed', 'node', 'ckeditor');

  /**
   * The test user.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $webUser;

  /**
   * The test Flickr URL.
   */
  const FLICKR_URL = 'http://www.flickr.com/photos/bees/2341623661/';

  /**
   * The expected output of the Flickr URL.
   */
  const FLICKR_OUTPUT = '<a data-flickr-embed="true" href="https://www.flickr.com/photos/bees/2341623661/" title="ZB8T0193 by ‮‭‬bees‬, on Flickr"><img src="https://farm4.staticflickr.com/3123/2341623661_7c99f48bbf_b.jpg" width="1024" height="683" alt="ZB8T0193" /></a><script async="" src="https://embedr.flickr.com/assets/client-code.js" charset="utf-8"></script>';

  /**
   * A set up for all tests.
   */
  protected function setUp() {
    parent::setUp();

    // Create a page content type.
    $this->drupalCreateContentType(array('type' => 'page', 'name' => 'Basic page'));


    // Create a text format and enable the url_embed filter.
    $format = FilterFormat::create([
      'format' => 'custom_format',
      'name' => 'Custom format',
      'filters' => [
        'url_embed' => [
          'status' => 1,
        ],
      ],
    ]);
    $format->save();

    $editor_group = [
      'name' => 'URL Embed',
      'items' => [
        'url',
      ],
    ];
    $editor = Editor::create([
      'format' => 'custom_format',
      'editor' => 'ckeditor',
      'settings' => [
        'toolbar' => [
          'rows' => [[$editor_group]],
        ],
      ],
    ]);
    $editor->save();

    // Create a user with required permissions.
    $this->webUser = $this->drupalCreateUser(array(
      'access content',
      'create page content',
      'use text format custom_format',
    ));
    $this->drupalLogin($this->webUser);
  }

}
