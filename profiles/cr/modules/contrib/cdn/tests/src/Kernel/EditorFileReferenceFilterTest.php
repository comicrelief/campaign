<?php

namespace Drupal\Tests\cdn\Kernel;

use Drupal\file\Entity\File;
use Drupal\filter\FilterPluginCollection;
use Drupal\KernelTests\KernelTestBase;

/**
 * Tests the CDN module's Editor module's file reference filter.
 *
 * @group cdn
 *
 * @see \Drupal\Tests\editor\Kernel\EditorFileReferenceFilterTest
 */
class EditorFileReferenceFilterTest extends KernelTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['system', 'filter', 'editor', 'field', 'file', 'user', 'cdn'];

  /**
   * @var \Drupal\filter\Plugin\FilterInterface[]
   */
  protected $filters;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->installConfig(['system', 'cdn']);
    $this->config('cdn.settings')
      ->set('mapping', ['type' => 'simple', 'domain' => 'cdn-a.com'])
      // Disable the farfuture functionality: simpler file URL assertions.
      ->set('farfuture', ['status' => FALSE])
      ->save();
    $this->installEntitySchema('file');
    $this->installSchema('file', ['file_usage']);

    $manager = $this->container->get('plugin.manager.filter');
    $bag = new FilterPluginCollection($manager, []);
    $this->filters = $bag->getAll();
  }

  /**
   * Enables CDN integration.
   */
  protected function disableCdn() {
    $this->config('cdn.settings')->set('status', FALSE)->save();
  }

  /**
   * Disables CDN integration.
   */
  protected function enableCdn() {
    $this->config('cdn.settings')->set('status', TRUE)->save();
  }

  /**
   * Tests the editor file reference filter.
   *
   * Verifies that it works as expected when CDN integration is enabled, but
   * also when it is disabled: this ensures that we know whether core breaks.
   *
   * @see \Drupal\Tests\editor\Kernel\EditorFileReferenceFilterTest::testEditorFileReferenceFilter()
   */
  public function testEditorFileReferenceFilter() {
    $filter = $this->filters['editor_file_reference'];

    $test = function ($input) use ($filter) {
      return $filter->process($input, 'und');
    };

    file_put_contents('public://llama.jpg', $this->randomMachineName());
    $image = File::create(['uri' => 'public://llama.jpg']);
    $image->save();
    $uuid = $image->uuid();

    $this->assertTrue(TRUE, 'Simple case.');
    $input = '<img src="llama.jpg" data-entity-type="file" data-entity-uuid="' . $uuid . '" />';
    $expected_output = '<img src="/' . $this->siteDirectory . '/files/llama.jpg" data-entity-type="file" data-entity-uuid="' . $uuid . '" />';
    $output = $test($input);
    $this->assertSame($expected_output, $output->getProcessedText());
    $this->enableCdn();
    $expected_output = '<img src="//cdn-a.com/' . $this->siteDirectory . '/files/llama.jpg" data-entity-type="file" data-entity-uuid="' . $uuid . '" />';
    $output = $test($input);
    $this->assertSame($expected_output, $output->getProcessedText());
    $this->disableCdn();

    $this->assertTrue(TRUE, 'Two identical cases, must result in identical CDN file URLs.');
    $input = '<img src="llama.jpg" data-entity-type="file" data-entity-uuid="' . $uuid . '" />';
    $input .= '<img src="llama.jpg" data-entity-type="file" data-entity-uuid="' . $uuid . '" />';
    $expected_output = '<img src="/' . $this->siteDirectory . '/files/llama.jpg" data-entity-type="file" data-entity-uuid="' . $uuid . '" />';
    $expected_output .= '<img src="/' . $this->siteDirectory . '/files/llama.jpg" data-entity-type="file" data-entity-uuid="' . $uuid . '" />';
    $output = $test($input);
    $this->assertSame($expected_output, $output->getProcessedText());
    $this->enableCdn();
    $expected_output = '<img src="//cdn-a.com/' . $this->siteDirectory . '/files/llama.jpg" data-entity-type="file" data-entity-uuid="' . $uuid . '" />';
    $expected_output .= '<img src="//cdn-a.com/' . $this->siteDirectory . '/files/llama.jpg" data-entity-type="file" data-entity-uuid="' . $uuid . '" />';
    $output = $test($input);
    $this->assertSame($expected_output, $output->getProcessedText());
    $this->disableCdn();
  }

}
