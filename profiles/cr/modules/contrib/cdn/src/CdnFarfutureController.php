<?php

namespace Drupal\cdn;

use Drupal\Component\Utility\Crypt;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\PrivateKey;
use Drupal\Core\Site\Settings;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class CdnFarfutureController implements ContainerInjectionInterface {

  /**
   * The private key service.
   *
   * @var \Drupal\Core\PrivateKey
   */
  protected $privateKey;

  /**
   * @param \Drupal\Core\PrivateKey $private_key
   *   The private key service.
   */
  public function __construct(PrivateKey $private_key) {
    $this->privateKey = $private_key;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('private_key')
    );
  }

  /**
   * Serves the requested file with optimal far future expiration headers.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current request. $request->query must have root_relative_file_url,
   *   set by \Drupal\cdn\PathProcessor\CdnFarfuturePathProcessor.
   * @param string $security_token
   *   The security token. Ensures that users can not request any file they want
   *   by manipulating the URL (they could otherwise request settings.php for
   *   example). See https://www.drupal.org/node/1441502.
   * @param int $mtime
   *   The file's mtime.
   *
   * @returns \Symfony\Component\HttpFoundation\BinaryFileResponse
   *   The response that will efficiently send the requested file.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
   *   Thrown when the 'root_relative_file_url' query argument is not set, which
   *   can only happen in case of malicious requests or in case of a malfunction
   *   in \Drupal\cdn\PathProcessor\CdnFarfuturePathProcessor.
   * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
   *   Thrown when an invalid security token is provided.
   */
  public function download(Request $request, $security_token, $mtime) {
    // Ensure \Drupal\cdn\PathProcessor\CdnFarfuturePathProcessor did its job.
    if (!$request->query->has('root_relative_file_url')) {
      throw new BadRequestHttpException();
    }

    // Validate security token.
    $root_relative_file_url = $request->query->get('root_relative_file_url');
    $calculated_token = Crypt::hmacBase64($mtime . $root_relative_file_url, $this->privateKey->get() . Settings::getHashSalt());
    if ($security_token !== $calculated_token) {
      throw new AccessDeniedHttpException('Invalid security token.');
    }

    $farfuture_headers = [
      // Instead of being powered by PHP, tell the world this resource was
      // powered by the CDN module!
      'X-Powered-By' => 'Drupal CDN module (https://www.drupal.org/project/cdn)',
      // Browsers that implement the W3C Access Control specification might
      // refuse to use certain resources such as fonts if those resources
      // violate the same-origin policy. Send a header to explicitly allow
      // cross-domain use of those resources. (This is called Cross-Origin
      // Resource Sharing, or CORS.)
      // The CDN module allows any domain to access it by default, which means
      // hotlinking of these files is possible. If you want to prevent this,
      // implement a KernelEvents::RESPONSE subscriber that modifies this header
      // for this route.
      'Access-Control-Allow-Origin' => '*',
      'Access-Control-Allow-Methods' => 'GET, HEAD',
      // Set a far future Cache-Control header (480 weeks), which prevents
      // intermediate caches from transforming the data and allows any
      // intermediate cache to cache it, since it's marked as a public resource.
      'Cache-Control' => 'max-age=290304000, no-transform, public',
      // Set a far future Expires header. The maximum UNIX timestamp is
      // somewhere in 2038. Set it to a date in 2037, just to be safe.
      'Expires' => 'Tue, 20 Jan 2037 04:20:42 GMT',
      // Pretend the file was last modified a long time ago in the past, this
      // will prevent browsers that don't support Cache-Control nor Expires
      // headers to still request a new version too soon (these browsers
      // calculate a heuristic to determine when to request a new version, based
      // on the last time the resource has been modified).
      // Also see http://code.google.com/speed/page-speed/docs/caching.html.
      'Last-Modified' => 'Wed, 20 Jan 1988 04:20:42 GMT',
    ];

    $response = new BinaryFileResponse(substr($root_relative_file_url, 1), 200, $farfuture_headers, TRUE, NULL, FALSE, FALSE);
    $response->isNotModified($request);
    return $response;
  }

}
