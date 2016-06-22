<?php

namespace Drupal\fast404\EventSubscriber;

use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\fast404\Fast404;

class Fast404EventSubscriber implements EventSubscriberInterface {

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  public $requestStack;

  /**
   * Constructs a new Fast404EventSubscriber instance.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   The Request Stack.
   */
  public function __construct(RequestStack $request_stack) {
    $this->requestStack = $request_stack;
  }

  /**
   * Ensures Fast 404 output returned if applicable.
   */
  public function onKernelRequest(GetResponseEvent $event) {
    $request = $this->requestStack->getCurrentRequest();
    $fast_404 = new Fast404($request);

    $fast_404->extensionCheck();
    if ($fast_404->isPathBlocked()) {
      $event->setResponse($fast_404->response(TRUE));
    }

    $fast_404->pathCheck();
    if ($fast_404->isPathBlocked()) {
      $event->setResponse($fast_404->response(TRUE));
    }
  }

  /**
   * Registers the methods in this class that should be listeners.
   *
   * @return array
   *   An array of event listener definitions.
   */
  static function getSubscribedEvents() {
    $events[KernelEvents::REQUEST][] = array('onKernelRequest', 100);
    return $events;
  }

}
