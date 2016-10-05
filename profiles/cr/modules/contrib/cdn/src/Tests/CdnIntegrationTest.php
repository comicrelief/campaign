<?php

namespace Drupal\cdn\Tests;

use Drupal\Component\Utility\Crypt;
use Drupal\Core\Site\Settings;
use Drupal\file\Entity\File;
use Drupal\filter\Entity\FilterFormat;
use Drupal\Tests\BrowserTestBase;

/**
 * @group cdn
 */
class CdnIntegrationTest extends BrowserTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['node', 'cdn', 'file', 'editor'];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    // Create a text format that uses editor_file_reference, a node type with a
    // body field and image.
    $format = $this->randomMachineName();
    FilterFormat::create([
      'format' => $format,
      'name' => $this->randomString(),
      'weight' => 0,
      'filters' => [
        'editor_file_reference' => [
          'status' => 1,
          'weight' => 0,
        ],
      ],
    ])->save();
    $this->drupalCreateContentType([
      'type' => 'article',
      'name' => 'Article',
    ]);
    file_put_contents('public://druplicon.png', $this->randomMachineName());
    $image = File::create(['uri' => 'public://druplicon.png']);
    $image->save();
    $uuid = $image->uuid();

    // Create a node of the above node type using the above text format and
    // referencing the above image.
    $this->drupalCreateNode([
      'type' => 'article',
      'body' => [
        0 => [
          'value' => '<p>Do you also love Drupal?</p><img src="druplicon.png" data-caption="Druplicon" data-entity-type="file" data-entity-uuid="' . $uuid . '" />',
          'format' => $format,
        ],
      ],
    ]);

    // Configure CDN integration.
    $this->config('cdn.settings')
      ->set('mapping', ['type' => 'simple', 'domain' => 'cdn'])
      ->set('status', TRUE)
      // Disable the farfuture functionality: simpler file URL assertions.
      ->set('farfuture', ['status' => FALSE])
      ->save();
  }

  /**
   * Tests that uninstalling the CDN module causes CDN file URLs to disappear.
   */
  public function testUninstall() {
    $session = $this->getSession();

    $this->drupalGet('/node/1');
    $this->assertSame('MISS', $session->getResponseHeader('X-Drupal-Cache'));
    $this->assertSession()->responseContains('src="//cdn' . base_path() . $this->siteDirectory . '/files/druplicon.png"');
    $this->drupalGet('/node/1');
    $this->assertSame('HIT', $session->getResponseHeader('X-Drupal-Cache'));

    \Drupal::service('module_installer')->uninstall(['cdn']);
    $this->assertTrue(TRUE, 'Uninstalled CDN module.');

    $this->drupalGet('/node/1');
    $this->assertSame('MISS', $session->getResponseHeader('X-Drupal-Cache'));
    $this->assertSession()->responseContains('src="' . base_path() . $this->siteDirectory . '/files/druplicon.png"');
  }

  /**
   * Tests that the cdn.farfuture.download route/controller work as expected.
   */
  public function testFarfuture() {
    $drupal_js_mtime = filemtime(DRUPAL_ROOT . '/core/misc/drupal.js');
    $drupal_js_security_token = Crypt::hmacBase64($drupal_js_mtime . '/core/misc/drupal.js', \Drupal::service('private_key')->get() . Settings::getHashSalt());

    $this->drupalGet('/cdn/farfuture/' . $drupal_js_security_token . '/' . $drupal_js_mtime . '/core/misc/drupal.js');
    $this->assertSession()->statusCodeEquals(200);
    // Assert presence of headers that \Drupal\cdn\CdnFarfutureController sets.
    $this->assertSame('Wed, 20 Jan 1988 04:20:42 GMT', $this->getSession()->getResponseHeader('Last-Modified'));
    // Assert presence of headers that Symfony's BinaryFileResponse sets.
    $this->assertSame('bytes', $this->getSession()->getResponseHeader('Accept-Ranges'));

    // Any chance to the security token should cause a 403.
    $this->drupalGet('/cdn/farfuture/' . substr($drupal_js_security_token, 1) . '/' . $drupal_js_mtime . '/core/misc/drupal.js');
    $this->assertSession()->statusCodeEquals(403);
  }

}
