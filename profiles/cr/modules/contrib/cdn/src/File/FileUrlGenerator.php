<?php

namespace Drupal\cdn\File;

use Drupal\cdn\CdnSettings;
use Drupal\Component\Utility\Unicode;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\StreamWrapper\StreamWrapperInterface;
use Drupal\Core\StreamWrapper\StreamWrapperManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Generates CDN file URLs.
 *
 * @see https://www.drupal.org/node/2669074
 */
class FileUrlGenerator {

  /**
   * The file system service.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * The stream wrapper manager.
   *
   * @var \Drupal\Core\StreamWrapper\StreamWrapperManagerInterface
   */
  protected $streamWrapperManager;

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * The CDN settings service.
   *
   * @var \Drupal\cdn\CdnSettings
   */
  protected $settings;

  /**
   * Constructs a new CDN file URL generator object.
   *
   * @param \Drupal\Core\File\FileSystemInterface
   *   The file system service.
   * @param \Drupal\Core\StreamWrapper\StreamWrapperManagerInterface
   *   The stream wrapper manager.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   * @param \Drupal\cdn\CdnSettings $cdn_settings
   *   The CDN settings service.
   */
  public function __construct(FileSystemInterface $file_system, StreamWrapperManagerInterface $stream_wrapper_manager, RequestStack $request_stack, CdnSettings $cdn_settings) {
    $this->fileSystem = $file_system;
    $this->streamWrapperManager = $stream_wrapper_manager;
    $this->requestStack = $request_stack;
    $this->settings = $cdn_settings;
  }

  /**
   * Generates a CDN file URL for local files that are mapped to a CDN.
   *
   * Compatibility: normal paths and stream wrappers.
   *
   * There are two kinds of local files:
   * - "managed files", i.e. those stored by a Drupal-compatible stream wrapper.
   *   These are files that have either been uploaded by users or were generated
   *   automatically (for example through CSS aggregation).
   * - "shipped files", i.e. those outside of the files directory, which ship as
   *   part of Drupal core or contributed modules or themes.
   *
   * @param string $uri
   *   The URI to a file for which we need a CDN URL, or the path to a shipped
   *   file.
   *
   * @return string|FALSE
   *   A string containing the protocol-relative CDN file URI, or FALSE if this
   *   file URI should not be served from a CDN.
   */
  public function generate($uri) {
    if (!$this->settings->isEnabled()) {
      return FALSE;
    }

    $root_relative_url = $this->getRootRelativeUrl($uri);
    if ($root_relative_url === FALSE) {
      return FALSE;
    }

    // Extension-specific mapping.
    $file_extension = Unicode::strtolower(pathinfo($uri, PATHINFO_EXTENSION));
    $lookup_table = $this->settings->getLookupTable();
    if (isset($lookup_table[$file_extension])) {
      $key = $file_extension;
    }
    // Generic or fallback mapping.
    elseif (isset($lookup_table['*'])) {
      $key = '*';
    }
    // No mapping.
    else {
      return FALSE;
    }

    $result = $lookup_table[$key];

    // If there are multiple results, pick one using consistent hashing: ensure
    // the same file is always served from the same CDN domain.
    if (is_array($result)) {
      $filename = basename($uri);
      $hash = hexdec(substr(md5($filename), 0, 5));
      $cdn_domain = $result[$hash % count($result)];
    }
    else {
      $cdn_domain = $result;
    }

    return '//' . $cdn_domain . $root_relative_url;
  }

  /**
   * Gets the root-relative URL for files that are shipped or in a local stream.
   *
   * @param string $uri
   *   The URI to a file for which we need a CDN URL, or the path to a shipped
   *   file.
   *
   * @return bool|string
   *   Returns FALSE if the URI is not for a shipped file or in a local stream.
   *   Otherwise, returns the root-relative URL.
   */
  protected function getRootRelativeUrl($uri) {
    $scheme = $this->fileSystem->uriScheme($uri);

    // If the URI is absolute — HTTP(S) or otherwise — return early, except if
    // it's an absolute URI using a local stream wrapper scheme.
    if ($scheme && !isset($this->streamWrapperManager->getWrappers(StreamWrapperInterface::LOCAL)[$scheme])) {
      return FALSE;
    }
    // If the URI is protocol-relative, return early.
    elseif (Unicode::substr($uri, 0, 2) === '//') {
      return FALSE;
    }
    // The private:// stream wrapper is explicitly not supported.
    elseif ($scheme === 'private') {
      return FALSE;
    }

    $request = $this->requestStack->getCurrentRequest();

    return $scheme
      // Local stream wrapper.
      ? str_replace($request->getSchemeAndHttpHost(), '', $this->streamWrapperManager->getViaUri($uri)->getExternalUrl())
      // Shipped file.
      : $request->getBasePath() . '/' . $uri;
  }

}
