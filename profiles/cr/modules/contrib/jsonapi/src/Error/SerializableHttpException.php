<?php

namespace Drupal\jsonapi\Error;

use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Symfony\Component\HttpKernel\Exception\HttpException;

class SerializableHttpException extends HttpException {

  use DependencySerializationTrait;

}
