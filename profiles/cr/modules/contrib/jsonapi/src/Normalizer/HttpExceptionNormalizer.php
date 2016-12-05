<?php

namespace Drupal\jsonapi\Normalizer;

use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\jsonapi\Normalizer\Value\FieldItemNormalizerValue;
use Drupal\jsonapi\Normalizer\Value\HttpExceptionNormalizerValue;
use Drupal\serialization\Normalizer\NormalizerBase as SerializationNormalizerBase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class HttpExceptionNormalizer extends SerializationNormalizerBase {

  /**
   * The interface or class that this Normalizer supports.
   *
   * @var string
   */
  protected $supportedInterfaceOrClass = HttpExceptionInterface::class;

  /**
   * The current user making the request.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * HttpExceptionNormalizer constructor.
   *
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   The current user.
   */
  public function __construct(AccountProxyInterface $current_user) {
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public function normalize($object, $format = null, array $context = []) {
    /** @var $object \Symfony\Component\HttpKernel\Exception\HttpException */
    $error = [];
    $status_code = $object->getStatusCode();
    if (!empty(Response::$statusTexts[$status_code])) {
      $error['title'] = Response::$statusTexts[$status_code];
    }
    $error += [
      'status' => $status_code,
      'detail' => $object->getMessage(),
      'links' => [
        'info' => $this->getInfoUrl($status_code),
      ],
      'code' => $object->getCode(),
    ];
    if ($this->currentUser->hasPermission('access site reports')) {
      // The following information may contain sensitive information. Only show
      // it to authorized users.
      $error['source'] = [
        'file' => $object->getFile(),
        'line' => $object->getLine(),
      ];
      $error['meta'] = [
        'exception' => (string) $object,
        'trace' => $object->getTrace(),
      ];
    }

    return new HttpExceptionNormalizerValue(
      [new FieldItemNormalizerValue([$error])],
      FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED
    );
  }

  /**
   * Return a string to the common problem type.
   *
   * @return string
   *   URL pointing to the specific RFC-2616 section.
   */
  protected function getInfoUrl($status_code) {
    // Depending on the error code we'll return a different URL.
    $url = 'http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html';
    $sections = array(
      '100' => '#sec10.1.1',
      '101' => '#sec10.1.2',
      '200' => '#sec10.2.1',
      '201' => '#sec10.2.2',
      '202' => '#sec10.2.3',
      '203' => '#sec10.2.4',
      '204' => '#sec10.2.5',
      '205' => '#sec10.2.6',
      '206' => '#sec10.2.7',
      '300' => '#sec10.3.1',
      '301' => '#sec10.3.2',
      '302' => '#sec10.3.3',
      '303' => '#sec10.3.4',
      '304' => '#sec10.3.5',
      '305' => '#sec10.3.6',
      '307' => '#sec10.3.8',
      '400' => '#sec10.4.1',
      '401' => '#sec10.4.2',
      '402' => '#sec10.4.3',
      '403' => '#sec10.4.4',
      '404' => '#sec10.4.5',
      '405' => '#sec10.4.6',
      '406' => '#sec10.4.7',
      '407' => '#sec10.4.8',
      '408' => '#sec10.4.9',
      '409' => '#sec10.4.10',
      '410' => '#sec10.4.11',
      '411' => '#sec10.4.12',
      '412' => '#sec10.4.13',
      '413' => '#sec10.4.14',
      '414' => '#sec10.4.15',
      '415' => '#sec10.4.16',
      '416' => '#sec10.4.17',
      '417' => '#sec10.4.18',
      '500' => '#sec10.5.1',
      '501' => '#sec10.5.2',
      '502' => '#sec10.5.3',
      '503' => '#sec10.5.4',
      '504' => '#sec10.5.5',
      '505' => '#sec10.5.6',
    );
    return empty($sections[$status_code]) ? $url : $url . $sections[$status_code];
  }


}
