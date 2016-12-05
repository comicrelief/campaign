<?php

namespace Drupal\jsonapi\EventSubscriber;

use Drupal\serialization\EventSubscriber\DefaultExceptionSubscriber as SerializationDefaultExceptionSubscriber;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;

class DefaultExceptionSubscriber extends SerializationDefaultExceptionSubscriber {

  /**
   * {@inheritdoc}
   */
  protected static function getPriority() {
    return parent::getPriority() + 25;
  }

  /**
   * {@inheritdoc}
   */
  protected function setEventResponse(GetResponseForExceptionEvent $event, $status) {
    /** @var \Symfony\Component\HttpKernel\Exception\HttpException $exception */
    $exception = $event->getException();
    if ($exception instanceof HttpException) {
      $status = $status ?: $exception->getStatusCode();
    }
    $format = $event->getRequest()->getRequestFormat();
    $encoded_content = $this->serializer->serialize($exception, $format);
    $response = new Response($encoded_content, $status);
    $event->setResponse($response);
  }

}
