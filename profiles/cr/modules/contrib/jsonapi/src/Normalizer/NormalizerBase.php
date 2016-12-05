<?php

namespace Drupal\jsonapi\Normalizer;

use Drupal\hal\Normalizer\NormalizerBase as HalNormalizerBase;

/**
 * Class NormalizerBase.
 *
 * @package Drupal\jsonapi\Normalizer
 */
abstract class NormalizerBase extends HalNormalizerBase {

  /**
   * The formats that the Normalizer can handle.
   *
   * @var array
   */
  protected $formats = array('api_json');

  /**
   * The resource manager.
   *
   * @var \Drupal\jsonapi\Configuration\ResourceManagerInterface
   */
  protected $resourceManager;

}
