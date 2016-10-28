<?php

/**
 * @file
 * Contains \Drupal\monolog\Logger\Processor\RefererProcessor.
 */

namespace Drupal\monolog\Logger\Processor;

/**
 * Class RefererProcessor
 */
class RefererProcessor extends AbstractRequestProcessor {

  /**
   * @param array $record
   *
   * @return array
   */
  public function __invoke(array $record) {
    if ($request = $this->getRequest()) {
      $record['extra']['referer'] = $request->headers->get('Referer', '');
    }

    return $record;
  }

}
