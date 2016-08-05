<?php

/**
 * @file
 * Contains Drupal\url_embed\UrlEmbed.
 */

namespace Drupal\url_embed;

use Embed\Embed;

/**
 * A service class for handling URL embeds.
 */
class UrlEmbed implements UrlEmbedInterface {

  /**
   * @var array
   */
  public $config;

  /**
   * @{inheritdoc}
   */
  public function __construct(array $config = []) {
    $this->config = $config;
  }

  /**
   * @{inheritdoc}
   */
  public function getConfig() {
    return $this->config;
  }

  /**
   * @{inheritdoc}
   */
  public function setConfig(array $config) {
    $this->config = $config;
  }

  /**
   * @{inheritdoc}
   */
  public function getEmbed($request, array $config = []) {
    return Embed::create($request, $config ?: $this->config);
  }

}
