<?php

namespace Drupal\Tests\cdn\Unit\File;

use Drupal\cdn\CdnSettings;
use Drupal\cdn\File\FileUrlGenerator;
use Drupal\Core\File\FileSystem;
use Drupal\Core\Site\Settings;
use Drupal\Core\StreamWrapper\PublicStream;
use Drupal\Core\StreamWrapper\StreamWrapperInterface;
use Drupal\Core\StreamWrapper\StreamWrapperManagerInterface;
use Drupal\Tests\UnitTestCase;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @coversDefaultClass \Drupal\cdn\File\FileUrlGenerator
 * @group cdn
 */
class FileUrlGeneratorTest extends UnitTestCase {

  /**
   * @covers ::generate
   * @dataProvider urlProvider
   */
  public function testGenerate($base_path, $uri, $expected_result) {
    $gen = $this->createFileUrlGenerator($base_path, [
      'status' => 2,
      'mapping' => [
        'type' => 'complex',
        'fallback_domain' => 'cdn.example.com',
        'domains' => [
          0 => [
            'type' => 'simple',
            'domain' => 'static.example.com',
            'conditions' => [
              'extensions' => ['css', 'js'],
            ],
          ],
          1 => [
            'type' => 'auto-balanced',
            'domains' => [
              'img1.example.com',
              'img2.example.com',
            ],
            'conditions' => [
              'extensions' => ['jpg', 'jpeg', 'png'],
            ],
          ]
        ],
      ],
    ]);
    $this->assertSame($expected_result, $gen->generate($uri));
  }

  public function urlProvider() {
    $cases_root = [
      'absolute URL' => ['http://example.com/llama.jpg', FALSE],
      'protocol-relative URL' => ['//example.com/llama.jpg', FALSE],
      'shipped file (fallback)' => ['core/misc/something.else', '//cdn.example.com/core/misc/something.else'],
      'shipped file (simple)' => ['core/misc/simple.css', '//static.example.com/core/misc/simple.css'],
      'shipped file (auto-balanced)' => ['core/misc/auto-balanced.png', '//img2.example.com/core/misc/auto-balanced.png'],
      'shipped file with querystring (e.g. in url() in CSS)' => ['core/misc/something.else?foo=bar&baz=qux', '//cdn.example.com/core/misc/something.else?foo=bar&baz=qux'],
      'shipped file with fragment (e.g. in url() in CSS)' => ['core/misc/something.else#llama', '//cdn.example.com/core/misc/something.else#llama'],
      'shipped file with querystring & fragment (e.g. in url() in CSS)' => ['core/misc/something.else?foo=bar&baz=qux#llama', '//cdn.example.com/core/misc/something.else?foo=bar&baz=qux#llama'],
      'managed public public file (fallback)' => ['public://something.else', '//cdn.example.com/sites/default/files/something.else'],
      'managed public public file (spublic public imple)' => ['public://simple.css', '//static.example.com/sites/default/files/simple.css'],
      'managed public public file (auto-balanced)' => ['public://auto-balanced.png', '//img2.example.com/sites/default/files/auto-balanced.png'],
      'managed private file (fallback)' => ['private://something.else', FALSE],
    ];

    $cases_subdir = [
      'absolute URL' => ['http://example.com/llama.jpg', FALSE],
      'protocol-relative URL' => ['//example.com/llama.jpg', FALSE],
      'shipped file (fallback)' => ['core/misc/feed.svg', '//cdn.example.com/subdir/core/misc/feed.svg'],
      'shipped file (simple)' => ['core/misc/simple.css', '//static.example.com/subdir/core/misc/simple.css'],
      'shipped file (auto-balanced)' => ['core/misc/auto-balanced.png', '//img2.example.com/subdir/core/misc/auto-balanced.png'],
      'shipped file with querystring (e.g. in url() in CSS)' => ['core/misc/something.else?foo=bar&baz=qux', '//cdn.example.com/subdir/core/misc/something.else?foo=bar&baz=qux'],
      'shipped file with fragment (e.g. in url() in CSS)' => ['core/misc/something.else#llama', '//cdn.example.com/subdir/core/misc/something.else#llama'],
      'shipped file with querystring & fragment (e.g. in url() in CSS)' => ['core/misc/something.else?foo=bar&baz=qux#llama', '//cdn.example.com/subdir/core/misc/something.else?foo=bar&baz=qux#llama'],
      'managed public file (fallback)' => ['public://something.else', '//cdn.example.com/subdir/sites/default/files/something.else'],
      'managed public file (simple)' => ['public://simple.css', '//static.example.com/subdir/sites/default/files/simple.css'],
      'managed public file (auto-balanced)' => ['public://auto-balanced.png', '//img2.example.com/subdir/sites/default/files/auto-balanced.png'],
      'managed private file (fallback)' => ['private://something.else', FALSE],
    ];

    $cases = [];
    assert('count($cases_root) === count($cases_subdir)');
    foreach ($cases_root as $description => $case) {
      $cases['root, ' . $description] = array_merge([''], $case);
    }
    foreach ($cases_subdir as $description => $case) {
      $cases['subdir, ' . $description] = array_merge(['/subdir'], $case);
    }
    return $cases;
  }

  /**
   * Creates a BigPipeResponseAttachmentsProcessor with mostly dummies.
   *
   * @param string $base_path
   *   The base path to let Request::getBasePath() return.
   * @param array $raw_config
   *   The raw config for the cdn.settings.yml config.
   *
   * @return \Drupal\cdn\File\FileUrlGenerator
   *   The FileUrlGenerator to test.
   */
  protected function createFileUrlGenerator($base_path, array $raw_config) {
    $request = $this->prophesize(Request::class);
    $request->getBasePath()
      ->willReturn($base_path);
    $request->getSchemeAndHttpHost()
      ->willReturn('http://example.com');
    $request_stack = $this->prophesize(RequestStack::class);
    $request_stack->getCurrentRequest()
      ->willReturn($request->reveal());

    // @todo make this more elegant: the current URI is normally stored on
    //       the PublicStream instance, but while it is prophesized, that does
    //       not seem possible.
    $current_uri = '';

    $public_stream_wrapper = $this->prophesize(PublicStream::class);
    $public_stream_wrapper->getExternalUrl()
      ->will(function () use ($base_path, &$current_uri) {
        return 'http://example.com' . $base_path . '/sites/default/files/' . substr($current_uri, 9);
      });
    $stream_wrapper_manager = $this->prophesize(StreamWrapperManagerInterface::class);
    $stream_wrapper_manager->getWrappers(StreamWrapperInterface::LOCAL)
      ->willReturn(['public' => TRUE, 'private' => TRUE]);
    $stream_wrapper_manager->getViaUri(Argument::that(function ($uri) {
      return substr($uri, 0, 9) === 'public://';
    }))
      ->will(function($args) use (&$public_stream_wrapper, &$current_uri) {
        $s = $public_stream_wrapper->reveal();
        $current_uri = $args[0];
        return $s;
      });

    return new FileUrlGenerator(
      new FileSystem(
        $this->prophesize(StreamWrapperManagerInterface::class)->reveal(),
        new Settings([]),
        $this->prophesize(LoggerInterface::class)->reveal()
      ),
      $stream_wrapper_manager->reveal(),
      $request_stack->reveal(),
      new CdnSettings($this->getConfigFactoryStub(['cdn.settings' => $raw_config]))
    );
  }

}
