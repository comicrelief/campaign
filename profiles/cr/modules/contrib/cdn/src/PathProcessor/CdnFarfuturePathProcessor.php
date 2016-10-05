<?php

namespace Drupal\cdn\PathProcessor;

use Drupal\Core\PathProcessor\InboundPathProcessorInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Defines a path processor to rewrite CDN farfuture URLs.
 *
 * As the route system does not allow arbitrary amount of parameters convert
 * the file path to a query parameter on the request.
 *
 * @see \Drupal\image\PathProcessor\PathProcessorImageStyles
 */
class CdnFarfuturePathProcessor implements InboundPathProcessorInterface {

  /**
   * {@inheritdoc}
   */
  public function processInbound($path, Request $request) {
    if (strpos($path, '/cdn/farfuture/') !== 0) {
      return $path;
    }

    // Parse the security token, mtime and root-relative file URL.
    $tail = substr($path, strlen('/cdn/farfuture/'));
    list($security_token, $mtime, $root_relative_file_url) = explode('/', $tail, 3);

    // Set the root-relative file URL as query parameter.
    $request->query->set('root_relative_file_url', '/' . $root_relative_file_url);

    // Return the same path, but without the trailing file.
    return "/cdn/farfuture/$security_token/$mtime";
  }

}
