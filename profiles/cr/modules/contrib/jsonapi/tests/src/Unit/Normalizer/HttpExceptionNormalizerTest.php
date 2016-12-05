<?php

namespace Drupal\Tests\jsonapi\Unit\Normalizer;

use Drupal\Core\Session\AccountProxyInterface;
use Drupal\jsonapi\Normalizer\HttpExceptionNormalizer;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Class HttpExceptionNormalizerTest.
 *
 * @package Drupal\Tests\jsonapi\Unit\Normalizer
 *
 * @coversDefaultClass \Drupal\jsonapi\Normalizer\HttpExceptionNormalizer
 *
 * @group jsonapi
 */
class HttpExceptionNormalizerTest extends UnitTestCase {

  /**
   * @covers ::normalize
   */
  public function testNormalize() {
    $exception = new AccessDeniedHttpException('lorem', NULL, 13);
    $current_user = $this->prophesize(AccountProxyInterface::class);
    $current_user->hasPermission('access site reports')->willReturn(TRUE);
    $normalizer = new HttpExceptionNormalizer($current_user->reveal());
    $normalized = $normalizer->normalize($exception, 'api_json');
    $normalized = $normalized->rasterizeValue();
    $error = $normalized[0];
    $this->assertNotEmpty($error['meta']);
    $this->assertNotEmpty($error['source']);
    $this->assertEquals(13, $error['code']);
    $this->assertEquals(403, $error['status']);
    $this->assertEquals('Forbidden', $error['title']);
    $this->assertEquals('lorem', $error['detail']);

    $current_user = $this->prophesize(AccountProxyInterface::class);
    $current_user->hasPermission('access site reports')->willReturn(FALSE);
    $normalizer = new HttpExceptionNormalizer($current_user->reveal());
    $normalized = $normalizer->normalize($exception, 'api_json');
    $normalized = $normalized->rasterizeValue();
    $error = $normalized[0];
    $this->assertTrue(empty($error['meta']));
    $this->assertTrue(empty($error['source']));
  }

}
