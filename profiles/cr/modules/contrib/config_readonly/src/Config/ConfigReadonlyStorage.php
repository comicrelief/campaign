<?php

namespace Drupal\config_readonly\Config;

use Drupal\Core\Config\CachedStorage;
use Drupal\Core\Config\ConfigImporter;
use Drupal\Core\Config\StorageInterface;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Lock\LockBackendInterface;
use Drupal\Core\Site\Settings;

use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Defines the ConfigReadonly storage controller which will fail on write
 * operations.
 */
class ConfigReadonlyStorage extends CachedStorage {

  /**
   * The used lock backend instance.
   *
   * @var \Drupal\Core\Lock\LockBackendInterface
   */
  protected $lock;

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * Constructs a new ConfigReadonlyStorage.
   *
   * @param \Drupal\Core\Config\StorageInterface $storage
   *   A configuration storage to be cached.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   A cache backend used to store configuration.
   * @param \Drupal\Core\Lock\LockBackendInterface $lock
   *   The lock backend to check if config imports are in progress.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   */
  public function __construct(StorageInterface $storage, CacheBackendInterface $cache, LockBackendInterface $lock, RequestStack $request_stack) {
    parent::__construct($storage, $cache);
    $this->lock = $lock;
    $this->requestStack = $request_stack;
  }

  /**
   * {@inheritdoc}
   */
  public function createCollection($collection) {
    return new static(
      $this->storage->createCollection($collection),
      $this->cache,
      $this->lock,
      $this->requestStack
    );
  }

  /**
   * {@inheritdoc}
   *
   * @throws Exception
   */
  public function write($name, array $data) {
    $this->checkLock();
    return parent::write($name, $data);
  }

  /**
   * {@inheritdoc}
   *
   * @throws Exception
   */
  public function delete($name) {
    $this->checkLock();
    return parent::delete($name);
  }

  /**
   * {@inheritdoc}
   *
   * @throws Exception
   */
  public function rename($name, $new_name) {
    $this->checkLock();
    return parent::rename($name, $new_name);
  }

  /**
   * {@inheritdoc}
   *
   * @throws Exception
   */
  public function deleteAll($prefix = '') {
    $this->checkLock();
    return parent::deleteAll($prefix);
  }

  protected function checkLock() {
    // If settings.php says to lock config changes and if the config importer
    // isn't running (we do not want to lock config imports), then throw an
    // exception.
    // @see \Drupal\Core\Config\ConfigImporter::alreadyImporting()
    if (Settings::get('config_readonly') && $this->lock->lockMayBeAvailable(ConfigImporter::LOCK_NAME)) {
      $request = $this->requestStack->getCurrentRequest();
      if ($request && $request->attributes->get(RouteObjectInterface::ROUTE_NAME) === 'system.db_update') {
        // We seem to be in the middle of running update.php
        // @see \Drupal\Core\Update\UpdateKernel::setupRequestMatch()
        // @todo - always allow or support a flag for blocking it?
        return;
      }
      throw new \Exception('Your site configuration active store is currently locked.');
    }
  }

}
