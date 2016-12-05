<?php

namespace Drupal\jsonapi\Routing\Param;

/**
 * Class JsonApiParamBase.
 *
 * @package Drupal\jsonapi\Routing\Param
 */
class JsonApiParamBase implements JsonApiParamInterface {

  /**
   * The original data.
   *
   * @var string|string[]
   */
  protected $original;

  /**
   * The expanded data.
   *
   * @var string|string[]
   */
  protected $data;

  /**
   * Create a parameter object.
   *
   * @param string|string[] $original
   *   The user generated data.
   */
  public function __construct($original) {
    $this->original = $original;
  }

  /**
   * {@inheritdoc}
   */
  public function get() {
    if (!$this->data) {
      $this->data = $this->expand();
    }
    return $this->data;
  }

  /**
   * {@inheritdoc}
   */
  public function getOriginal() {
    return $this->original;
  }

  /**
   * {@inheritdoc}
   */
  public function getKey() {
    return static::KEY_NAME;
  }

  /**
   * Apply all necessary defaults and transformations to the parameter.
   *
   * @return string|string[]
   *   The expanded data.
   */
  protected function expand() {
    // The base implementation does no expansion.
    return $this->original;
  }

}
