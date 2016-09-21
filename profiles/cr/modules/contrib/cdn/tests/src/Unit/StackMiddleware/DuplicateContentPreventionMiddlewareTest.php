<?php

namespace Drupal\Tests\cdn\Unit\StackMiddleware;

use Drupal\cdn\StackMiddleware\DuplicateContentPreventionMiddleware;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\Utility\UnroutedUrlAssemblerInterface;
use Drupal\Tests\UnitTestCase;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * @coversDefaultClass \Drupal\cdn\StackMiddleware\DuplicateContentPreventionMiddleware
 * @group StackMiddleware
 * @group cdn
 */
class DuplicateContentPreventionMiddlewareTest extends UnitTestCase {

  /**
   * @covers ::handle
   * @covers ::getRedirectUrl
   * @dataProvider duplicateContentPreventionProvider
   */
  public function testDuplicateContentPrevention($path, $user_agent, $expected_redirect) {
    // The incoming request for the given path from the given user agent.
    $request_prophecy = $this->prophesize(Request::class);
    $request_prophecy->getPathInfo()
      ->willReturn($path);
    $request = $request_prophecy->reveal();
    $request->headers = new HeaderBag(['User-Agent' => $user_agent]);

    // Simulate the logic of the unrouted URL assembler.
    $container = new ContainerBuilder();
    $url_assembler_prophecy = $this->prophesize(UnroutedUrlAssemblerInterface::class);
    $url_assembler_prophecy->assemble(Argument::type('string'), ['absolute' => TRUE], FALSE)
      ->will(function ($args) {
        return str_replace('base:', 'http://🐷.com', $args[0]);
      });
    $container->set('unrouted_url_assembler', $url_assembler_prophecy->reveal());
    \Drupal::setContainer($container);

    // Mock the kernel to decorate.
    $kernel = $this->prophesize(HttpKernelInterface::class);
    $kernel->handle($request, HttpKernelInterface::MASTER_REQUEST, TRUE)
      ->willReturn(new Response());

    $middleware = new DuplicateContentPreventionMiddleware($kernel->reveal(), new RequestStack());
    $response = $middleware->handle($request);

    if ($expected_redirect !== FALSE) {
      $this->assertInstanceOf(RedirectResponse::class, $response);
      $this->assertSame(301, $response->getStatusCode());
      $this->assertSame('<' . $expected_redirect . '>; rel="canonical"', $response->headers->get('Link'));
    }
    else {
      $this->assertNotInstanceOf(RedirectResponse::class, $response);
    }
  }

  public function duplicateContentPreventionProvider() {
    return [
      // HTML requested: the response is a redirect when requested by a CDN.
      ['/',            'Mozilla', FALSE],
      ['/node/1',      'Mozilla', FALSE],
      ['/node/1.html', 'Mozilla', FALSE],
      ['/node/1.htm',  'Mozilla', FALSE],
      ['/node/1.php',  'Mozilla', FALSE],
      ['/',            'Amazon CloudFront', 'http://🐷.com/'],
      ['/node/1',      'Amazon CloudFront', 'http://🐷.com/node/1'],
      ['/node/1.html', 'Amazon CloudFront', 'http://🐷.com/node/1.html'],
      ['/node/1.htm',  'Amazon CloudFront', 'http://🐷.com/node/1.htm'],
      ['/node/1.php',  'Amazon CloudFront', 'http://🐷.com/node/1.php'],
      // File requested: the response is never a redirect.
      ['/misc/jquery.js', 'Mozilla', FALSE],
      ['/misc/jquery.js', 'Amazon CloudFront', FALSE],
      // Generated file requested: the response is never a redirect.
      ['/sites/default/files/styles/thumbnail/foobar.png', 'Mozilla', FALSE],
      ['/sites/default/files/styles/thumbnail/foobar.png', 'Amazon CloudFront', FALSE],
    ];
  }

}
