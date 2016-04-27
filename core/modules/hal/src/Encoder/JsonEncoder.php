<?php

/**
 * @file
 * Contains \Drupal\hal\Encoder\JsonEncoder.
 */

namespace Drupal\hal\Encoder;

use Symfony\Component\Serializer\Encoder\JsonEncoder as SymfonyJsonEncoder;

/**
 * Encodes HAL data in JSON.
 *
 * Simply respond to hal_json format requests using the JSON encoder.
 */
class JsonEncoder extends SymfonyJsonEncoder {

  /**
   * The formats that this Encoder supports.
   *
   * @var string
   */
  protected $format = 'hal_json';

  /**
   * {@inheritdoc}
   */
  public function supportsEncoding($format) {
    return $format == $this->format;
  }

  /**
   * {@inheritdoc}
   */
  public function supportsDecoding($format) {
    return $format == $this->format;
  }

}
