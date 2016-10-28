<?php

/**
 * @file
 * Contains \Drupal\monolog\Logger\Processor\IpProcessor.
 */

namespace Drupal\monolog\Logger\Processor;

/**
 * Class IpProcessor
 */
class IpProcessor extends AbstractRequestProcessor {

  /**
   * @param array $record
   *
   * @return array
   */
  public function __invoke(array $record) {
    if ($request = $this->getRequest()) {
      $record['extra']['ip'] = $request->getClientIp();
    }

    return $record;
  }

}
