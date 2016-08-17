<?php

/**
 * @file
 * Contains \Drupal\media_entity_instagram\Tests\InstagramEmbedFormatterTest.
 */

namespace Drupal\media_entity_instagram\Tests;

use Drupal\simpletest\WebTestBase;
use Drupal\media_entity\Tests\MediaTestTrait;

/**
 * Tests for Instagram embed formatter.
 *
 * @group media_entity_instagram
 */
class InstagramEmbedFormatterTest extends WebTestBase {

  use MediaTestTrait;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array(
    'media_entity_instagram',
    'media_entity',
    'node',
    'field_ui',
    'views_ui',
    'block',
  );

  /**
   * The test user.
   *
   * @var \Drupal\User\UserInterface
   */
  protected $adminUser;

  /**
   * Media entity machine id.
   *
   * @var string
   */
  protected $media_id = 'instagram';

  /**
   * The test media bundle.
   *
   * @var \Drupal\media_entity\MediaBundleInterface
   */
  protected $testBundle;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $bundle['bundle'] = $this->media_id;
    $this->testBundle = $this->drupalCreateMediaBundle($bundle, 'instagram');
    $this->drupalPlaceBlock('local_actions_block');
    $this->adminUser = $this->drupalCreateUser([
      'administer media',
      'administer media fields',
      'administer media form display',
      'administer media display',
      // Media entity permissions.
      'view media',
      'create media',
      'update media',
      'update any media',
      'delete media',
      'delete any media',
      // Other permissions.
      'administer views',
    ]);
    $this->drupalLogin($this->adminUser);
  }

  /**
   * Tests adding and editing an instagram embed formatter.
   */
  public function testManageFieldFormatter() {
    // Test and create one media bundle.
    $bundle = $this->testBundle;

    // Assert that the media bundle has the expected values before proceeding.
    $this->drupalGet('admin/structure/media/manage/' . $bundle->id());
    $this->assertFieldByName('label', $bundle->label());
    $this->assertFieldByName('type', 'instagram');

    // Add and save field settings (Embed code).
    $this->drupalGet('admin/structure/media/manage/' . $bundle->id() . '/fields/add-field');
    $edit_conf = [
      'new_storage_type' => 'string_long',
      'label' => 'Embed code',
      'field_name' => 'embed_code',
    ];
    $this->drupalPostForm(NULL, $edit_conf, t('Save and continue'));
    $this->assertText('These settings apply to the ' . $edit_conf['label'] . ' field everywhere it is used.');
    $edit = [
      'cardinality' => 'number',
      'cardinality_number' => '1',
    ];
    $this->drupalPostForm(NULL, $edit, t('Save field settings'));
    $this->assertText('Updated field ' . $edit_conf['label'] . ' field settings.');

    // Set the new field as required.
    $edit = [
      'required' => TRUE,
    ];
    $this->drupalPostForm(NULL, $edit, t('Save settings'));
    $this->assertText('Saved ' . $edit_conf['label'] . ' configuration.');

    // Assert that the new field configuration has been successfully saved.
    $xpath = $this->xpath('//*[@id="field-embed-code"]');
    $this->assertEqual((string) $xpath[0]->td[0], 'Embed code');
    $this->assertEqual((string) $xpath[0]->td[1], 'field_embed_code');
    $this->assertEqual((string) $xpath[0]->td[2]->a, 'Text (plain, long)');

    // Test if edit worked and if new field values have been saved as
    // expected.
    $this->drupalGet('admin/structure/media/manage/' . $bundle->id());
    $this->assertFieldByName('label', $bundle->label());
    $this->assertFieldByName('type', 'instagram');
    $this->assertFieldByName('type_configuration[instagram][source_field]', 'field_embed_code');
    $this->drupalPostForm(NULL, NULL, t('Save media bundle'));
    $this->assertText('The media bundle ' . $bundle->label() . ' has been updated.');
    $this->assertText($bundle->label());

    $this->drupalGet('admin/structure/media/manage/' . $bundle->id() . '/display');

    // Set and save the settings of the new field.
    $edit = [
      'fields[field_embed_code][label]' => 'above',
      'fields[field_embed_code][type]' => 'instagram_embed',
    ];
    $this->drupalPostForm(NULL, $edit, t('Save'));
    $this->assertText('Your settings have been saved.');

    // Create and save the media with an instagram media code.
    $this->drupalGet('media/add/' . $bundle->id());

    // Random image from instagram.
    $instagram = "<blockquote class='instagram-media' " .
      "data-instgrm-captioned data-instgrm-version='6' " .
      "style=' background:#FFF; border:0; border-radius:3px; box-shadow:0 0 1px 0 " .
      "rgba(0,0,0,0.5),0 1px 10px 0 rgba(0,0,0,0.15); margin: 1px; max-width:658px; " .
      "padding:0; width:99.375%; width:-webkit-calc(100% - 2px); width:calc(100% - 2px);'>" .
      "<div style='padding:8px;'><div style=' background:#F8F8F8; line-height:0; margin-top:40px; padding:50.0% 0; text-align:center; width:100%;'> <div style=' background:url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACwAAAAsCAMAAAApWqozAAAAGFBMVEUiIiI9PT0eHh4gIB4hIBkcHBwcHBwcHBydr+JQAAAACHRSTlMABA4YHyQsM5jtaMwAAADfSURBVDjL7ZVBEgMhCAQBAf//42xcNbpAqakcM0ftUmFAAIBE81IqBJdS3lS6zs3bIpB9WED3YYXFPmHRfT8sgyrCP1x8uEUxLMzNWElFOYCV6mHWWwMzdPEKHlhLw7NWJqkHc4uIZphavDzA2JPzUDsBZziNae2S6owH8xPmX8G7zzgKEOPUoYHvGz1TBCxMkd3kwNVbU0gKHkx+iZILf77IofhrY1nYFnB/lQPb79drWOyJVa/DAvg9B/rLB4cC+Nqgdz/TvBbBnr6GBReqn/nRmDgaQEej7WhonozjF+Y2I/fZou/qAAAAAElFTkSuQmCC); display:block; height:44px; margin:0 auto -44px; position:relative; top:-22px; width:44px;'></div></div> " .
      "<p style=' margin:8px 0 0 0; padding:0 4px;'> <a href='https://www.instagram.com/p/-rm1I2s7D5/' " .
      "style=' color:#000; font-family:Arial,sans-serif; font-size:14px; font-style:normal; font-weight:normal; line-height:17px; text-decoration:none; word-wrap:break-word;' target='_blank'>Since I haven&#39;t posted in awhile!!! " .
      "#like4like#shoutout4shoutout#spamforspam#follow4follow#itssomething</a></p> " .
      "<p style=' color:#c9c8cd; font-family:Arial,sans-serif; font-size:14px; line-height:17px; margin-bottom:0; margin-top:8px; overflow:hidden; padding:8px 0 7px; text-align:center; text-overflow:ellipsis; white-space:nowrap;'>Una foto pubblicata da @tyler.from.winfield in data: " .
      "<time style=' font-family:Arial,sans-serif; font-size:14px; line-height:17px;' " .
      "datetime='2015-11-29T20:00:06+00:00'>29 Nov 2015 alle ore 12:00 PST</time></p></div></blockquote>" .
      "<script async defer src='//platform.instagram.com/en_US/embeds.js'></script>";

    $edit = [
      'name[0][value]' => 'Title',
      'field_embed_code[0][value]' => $instagram,
    ];
    $this->drupalPostForm(NULL, $edit, t('Save'));

    // Assert that the media has been successfully saved.
    $this->assertText('Title');
    $this->assertText('Embed code');

    // Assert that the formatter exists on this page.
    $this->assertFieldByXPath('/html/body/div/main/div/div/article/div[5]/div[2]/iframe');
  }

}
