<?php

/**
 * @file
 * Contains \Drupal\monolog\Logger\Processor\CurrentUserProcessor.
 */

namespace Drupal\monolog\Logger\Processor;

use Drupal\Core\Session\AccountProxyInterface;

/**
 * Class UidProcessor
 */
class CurrentUserProcessor {

  /**
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $accountProxy;

  /**
   * @param \Drupal\Core\Session\AccountProxyInterface $account_proxy
   */
  public function __construct(AccountProxyInterface $account_proxy) {
    $this->accountProxy = $account_proxy;
  }

  /**
   * @param array $record
   *
   * @return array
   */
  public function __invoke(array $record) {
    $record['extra']['uid'] = $this->accountProxy->id();
    $record['extra']['user'] = $this->accountProxy->getAccountName();

    return $record;
  }

}
