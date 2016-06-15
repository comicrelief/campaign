<?php

/**
 * @file
 * Contains \Drupal\monolog\Logger\Processor\RequestUriProcessor.
 */

namespace Drupal\monolog\Logger\Processor;

/**
 * Class RequestUriProcessor.php
 */
class RequestUriProcessor extends AbstractRequestProcessor {

  /**
   * @param array $record
   *
   * @return array
   */
  public function __invoke(array $record) {
    if ($request = $this->getRequest()) {
      $record['extra']['request_uri'] = $request->getUri();
    }

    return $record;
  }

}
