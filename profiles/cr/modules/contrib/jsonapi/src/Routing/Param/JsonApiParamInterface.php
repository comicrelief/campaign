<?php


namespace Drupal\jsonapi\Routing\Param;

/**
 * Class JsonApiParamInterface.
 *
 * @package Drupal\jsonapi\Routing\Param
 */
interface JsonApiParamInterface {

  /**
   * The key name.
   *
   * @var string
   */
  const KEY_NAME = NULL;

  /**
   * Gets the original parsed query string param.
   *
   * @return string|string[]
   *   The original value.
   */
  public function getOriginal();

  /**
   * Gets the expanded value with defaults.
   *
   * @return string|string[]
   *   The query string value.
   */
  public function get();

  /**
   * Gets the key of the parameter.
   *
   * @return string
   *   The key.
   */
  public function getKey();

}
