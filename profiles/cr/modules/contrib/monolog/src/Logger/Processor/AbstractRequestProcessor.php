<?php

/**
 * @file
 * Contains \Drupal\monolog\Logger\Processor\AbstractRequestProcessor.
 */

namespace Drupal\monolog\Logger\Processor;

use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Base class for all processors that needs access to request data.
 */
abstract class AbstractRequestProcessor {

  /**
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  private $requestStack;

  /**
   * RequestProcessor constructor.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack
   */
  public function __construct(RequestStack $requestStack) {
    $this->requestStack = $requestStack;
  }

  /**
   * @return null|\Symfony\Component\HttpFoundation\Request
   */
  public function getRequest() {
    if ($this->requestStack && $request = $this->requestStack->getCurrentRequest()) {
      return $request;
    }

    return NULL;
  }
}
