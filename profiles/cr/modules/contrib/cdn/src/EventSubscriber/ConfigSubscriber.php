<?php

namespace Drupal\cdn\EventSubscriber;

use Drupal\Core\Cache\CacheTagsInvalidatorInterface;
use Drupal\Core\Config\ConfigCrudEvent;
use Drupal\Core\Config\ConfigEvents;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\DrupalKernelInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Invalidates cache tags & enables CSS aggregation when CDN config is saved.
 */
class ConfigSubscriber implements EventSubscriberInterface {

  use StringTranslationTrait;

  /**
   * The cache tags invalidator.
   *
   * @var \Drupal\Core\Cache\CacheTagsInvalidatorInterface
   */
  protected $cacheTagsInvalidator;

  /**
   * The Drupal kernel.
   *
   * @var \Drupal\Core\DrupalKernelInterface
   */
  protected $drupalKernel;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructs a ConfigSubscriber object.
   *
   * @param \Drupal\Core\Cache\CacheTagsInvalidatorInterface $cache_tags_invalidator
   *   The cache tags invalidator.
   * @param \Drupal\Core\DrupalKernelInterface $drupal_kernel
   *   The Drupal kernel.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   A config factory for retrieving config objects.
   */
  public function __construct(CacheTagsInvalidatorInterface $cache_tags_invalidator, DrupalKernelInterface $drupal_kernel, ConfigFactoryInterface $config_factory) {
    $this->cacheTagsInvalidator = $cache_tags_invalidator;
    $this->drupalKernel = $drupal_kernel;
    $this->configFactory = $config_factory;
  }

  /**
   * Invalidates all render caches when CDN settings are modified.
   *
   * @param \Drupal\Core\Config\ConfigCrudEvent $event
   *   The Event to process.
   */
  public function onSave(ConfigCrudEvent $event) {
    if ($event->getConfig()->getName() === 'cdn.settings') {
      // If farfuture was just enabled, then we must enable CSS aggregation.
      // Otherwise files referenced by the CSS (images, fonts …) will fail to
      // load, because they will reuse the security token of the referencing CSS
      // file. By enabling CSS aggregation, referenced files will be passed
      // through file_create_url(), giving them their own security tokens.
      if ($event->getConfig()->get('farfuture.status') === TRUE && $event->isChanged('farfuture.status') && !$this->configFactory->get('system.performance')->get('css.preprocess')) {
        $this->configFactory->getEditable('system.performance')
          ->set('css.preprocess', TRUE)
          ->save();
        drupal_set_message($this->t('Automatically enabled the <q>@css-aggregation-label</q> setting: this is required for the <q>@farfuture-label</q> functionality.', [
          '@css-aggregation-label' => $this->t('Aggregate CSS files'),
          '@farfuture-label' => $this->t('Forever cacheable files'),
        ]), 'warning');
      }

      $this->cacheTagsInvalidator->invalidateTags([
        // Rendered output that is cached. (HTML containing URLs.)
        'rendered',
        // Processed assets that are cached. (CSS aggregates containing URLs).
        'library_info',
      ]);

      // Rebuild the container whenever the 'status' configuration changes.
      // @see \Drupal\cdn\CdnServiceProvider
      if ($event->isChanged('status')) {
        $this->drupalKernel->invalidateContainer();
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[ConfigEvents::SAVE][] = ['onSave'];
    return $events;
  }

}
