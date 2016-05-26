<?php

/**
 * @file
 * Contains Drupal\url_embed\UrlEmbedHelperTrait.
 */

namespace Drupal\url_embed;

use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * Wrapper methods for URL embedding.
 *
 * This utility trait should only be used in application-level code, such as
 * classes that would implement ContainerInjectionInterface. Services registered
 * in the Container should not use this trait but inject the appropriate service
 * directly for easier testing.
 */
trait UrlEmbedHelperTrait {

  /**
   * The module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface.
   */
  protected $moduleHandler;

  /**
   * The URL embed service.
   *
   * @var \Drupal\url_embed\UrlEmbedService.
   */
  protected $url_embed;

  /**
   * Returns the module handler.
   *
   * @return \Drupal\Core\Extension\ModuleHandlerInterface
   *   The module handler.
   */
  protected function moduleHandler() {
    if (!isset($this->moduleHandler)) {
      $this->moduleHandler = \Drupal::moduleHandler();
    }
    return $this->moduleHandler;
  }

  /**
   * Sets the module handler service.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler service.
   *
   * @return self
   */
  public function setModuleHandler(ModuleHandlerInterface $module_handler) {
    $this->moduleHandler = $module_handler;
    return $this;
  }

  /**
   * Returns the URL embed service.
   *
   * @return \Drupal\url_embed\UrlEmbedInterface
   *   The URL embed service..
   */
  protected function urlEmbed() {
    if (!isset($this->url_embed)) {
      $this->url_embed = \Drupal::service('url_embed');
    }
    return $this->url_embed;
  }

  /**
   * Sets the URL embed service.
   *
   * @param \Drupal\url_embed\UrlEmbedInterface $url_embed
   *   The URL embed service.
   *
   * @return self
   */
  public function setUrlEmbed(UrlEmbedInterface $url_embed) {
    $this->url_embed = $url_embed;
    return $this;
  }
}
