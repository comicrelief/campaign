<?php

/**
 * @file
 * Contains \Drupal\video_embed_wysiwyg\Tests\EmbedDialogTest.
 */

namespace Drupal\video_embed_wysiwyg\Tests;

use Drupal\video_embed_field\Tests\WebTestBase;

/**
 * Test the dialog form.
 *
 * @group video_embed_wysiwyg
 */
class EmbedDialogTest extends WebTestBase {

  /**
   * Modules to install.
   *Video
   * @var array
   */
  public static $modules = [
    'video_embed_field',
    'video_embed_wysiwyg',
    'editor',
    'ckeditor',
    'field_ui',
    'node',
    'image',
  ];

  /**
   * An array of AJAX assertions to process in order.
   *
   * @var array
   */
  protected $ajaxAsserts = [];

  /**
   * Test the dialog form.
   */
  public function testFilterDialogForm() {
    $this->drupalLogin($this->adminUser);

    // Assert access is denied without enabling the filter.
    $this->drupalGet('video-embed-wysiwyg/dialog/plain_text');
    $this->assertResponse(403);

    // Enable the filter.
    $this->drupalGet('admin/config/content/formats/manage/plain_text');
    $this->drupalPostForm(NULL, [
      'filters[video_embed_wysiwyg][status]' => '1',
    ], 'Save configuration');

    // Visit the modal again.
    $this->drupalGet('video-embed-wysiwyg/dialog/plain_text');

    // Assert all the form fields appear on the modal.
    $this->assertText('Autoplay');
    $this->assertText('Responsive Video');
    $this->assertText('Width');
    $this->assertText('Height');
    $this->assertText('Video URL');

    // Attempt to submit the modal with no values.
    $this->drupalPostAjaxForm(NULL, [], 'op');
    $this->assertText('Video URL field is required.');

    // Submit the form with an invalid video URL.
    $this->drupalPostAjaxForm(NULL, [
      'video_url' => 'http://example.com/'
    ], 'op');
    $this->assertText('Could not find a video provider to handle the given URL.');
    $this->assertRaw('http://example.com/');

    // Assert the settings are sent to the client.
    $this->assertAjax('editorDialogSave', function ($command) {
      $this->assertEqual($command['values']['video_url'], 'https://www.youtube.com/watch?v=iaf3Sl2r3jE&t=1553s');
      $this->assertEqual($command['values']['settings'], [
        'responsive' => 0,
        'width' => '854',
        'height' => '480',
        'autoplay' => 1,
      ]);
      $this->assertEqual($command['values']['settings_summary'], ['Embedded Video (854x480, autoplaying).']);
    });

    // Assert the modal close command is sent.
    $this->assertAjax('closeDialog', function ($command) {
      $this->assertEqual($command['selector'], '#drupal-modal');
    });

    // Submit a valid URL.
    $this->drupalPostAjaxForm(NULL, [
      'video_url' => 'https://www.youtube.com/watch?v=iaf3Sl2r3jE&t=1553s'
    ], 'op');
  }

  /**
   * Add an item to the AJAX assertions.
   *
   * @param string $command
   *   The command to respond to.
   * @param callable $callback
   *   The callback to run assertions with.
   */
  protected function assertAjax($command, callable $callback) {
    $this->ajaxAsserts[] = [
      'command' => $command,
      'callback' => $callback,
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function drupalProcessAjaxResponse($content, array $ajax_response, array $ajax_settings, array $drupal_settings) {
    // Process the AJAX assertions as a stack.
    while ($assertion = array_pop($this->ajaxAsserts)) {
      $called = FALSE;
      foreach ($ajax_response as $command) {
        if ($command['command'] == $assertion['command']) {
          $assertion['callback']($command);
          $called = TRUE;
        }
      }
      if (!$called) {
        throw new \Exception('AJAX command was not found.');
      }
    }
    parent::drupalProcessAjaxResponse($content, $ajax_response, $ajax_settings, $drupal_settings);
  }

  /**
   * {@inheritdoc}
   */
  protected function tearDown() {
    if (count($this->ajaxAsserts) > 0) {
      throw new \Exception('AJAX commands were not handled.');
    }
    parent::tearDown();
  }

}
