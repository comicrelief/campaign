<?php

namespace Drupal\Tests\jsonapi\Kernel\Plugin;

use Drupal\file\Entity\File;
use Drupal\KernelTests\KernelTestBase;

/**
 * Class FileDownloadUrlTest
 *
 * @package Drupal\Tests\jsonapi\Kernel\Plugin
 *
 * @coversDefaultClass \Drupal\jsonapi\Plugin\FileDownloadUrl
 *
 * @group jsonapi
 */
class FileDownloadUrlTest extends KernelTestBase  {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'jsonapi',
    'file',
    'serialization',
    'user',
  ];

  /**
   * @var \Drupal\file\Entity\File
   */
  protected $file;

  /**
   * @var string
   *   The test filename.
   */
  protected $filename = 'druplicon.txt';

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->installEntitySchema('file');
    $this->installSchema('file', array('file_usage'));

    // Create a new file entity.
    $this->file = File::create(array(
      'filename' => $this->filename,
      'uri' => sprintf('public://%s', $this->filename),
      'filemime' => 'text/plain',
      'status' => FILE_STATUS_PERMANENT,
    ));

    $this->file->save();
  }

  /**
   * Test the URL computed field.
   */
  public function testUrlField() {
    $url_field = $this->file->get('url');
    // Test all the different ways to access a field item.
    $values = [
      $url_field->value,
      $url_field->getValue()[0]['value'],
      $url_field->get(0)->toArray()['value'],
      $url_field->first()->getValue()['value'],
    ];
    array_walk($values, function ($value) {
      $this->assertContains('simpletest', $value);
      $this->assertContains($this->filename, $value);
    });
  }

}
