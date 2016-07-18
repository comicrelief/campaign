<?php

namespace Drupal\devel\EventSubscriber;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Routing\UrlGeneratorInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class DevelEventSubscriber implements EventSubscriberInterface {

  /**
   * The devel.settings config object.
   *
   * @var \Drupal\Core\Config\Config;
   */
  protected $config;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $account;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * @var \Drupal\Core\Routing\UrlGeneratorInterface
   */
  protected $urlGenerator;

  /**
   * Constructs a DevelEventSubscriber object.
   */
  public function __construct(ConfigFactoryInterface $config, AccountInterface $account, ModuleHandlerInterface $module_handler, UrlGeneratorInterface $url_generator) {
    $this->config = $config->get('devel.settings');
    $this->account = $account;
    $this->moduleHandler = $module_handler;
    $this->urlGenerator = $url_generator;
  }

  /**
   * Register the devel error handler.
   *
   * @param \Symfony\Component\EventDispatcher\Event $event
   *   The event to process.
   */
  public function registerErrorHandler(Event $event = NULL) {
    if ($this->account && $this->account->hasPermission('access devel information')) {
      devel_set_handler(devel_get_handlers());
    }
  }

  /**
   * Initializes devel module requirements.
   */
  public function onRequest(GetResponseEvent $event) {
    if ($this->config->get('rebuild_theme')) {
      drupal_theme_rebuild();

      // Ensure that the active theme object is cleared.
      $theme_name = \Drupal::theme()->getActiveTheme()->getName();
      \Drupal::state()->delete('theme.active_theme.' . $theme_name);
      \Drupal::theme()->resetActiveTheme();

      /** @var \Drupal\Core\Extension\ThemeHandlerInterface $theme_handler*/
      $theme_handler = \Drupal::service('theme_handler');
      $theme_handler->refreshInfo();
      // @todo This is not needed after https://www.drupal.org/node/2330755
      $list = $theme_handler->listInfo();
      $theme_handler->addTheme($list[$theme_name]);

      if (\Drupal::service('flood')->isAllowed('devel.rebuild_theme_warning', 1)) {
        \Drupal::service('flood')->register('devel.rebuild_theme_warning');
        if ($this->account->hasPermission('access devel information')) {
          drupal_set_message(t('The theme information is being rebuilt on every request. Remember to <a href=":url">turn off</a> this feature on production websites.', array(':url' => $this->urlGenerator->generateFromRoute('devel.admin_settings'))));
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    // Set a low value to start as early as possible.
    $events[KernelEvents::REQUEST][] = ['onRequest', -100];

    // Runs as soon as possible in the request but after
    // AuthenticationSubscriber (priority 300) because you need to access to
    // the current user for determine whether register the devel error handler
    // or not.
    $events[KernelEvents::REQUEST][] = ['registerErrorHandler', 256];

    return $events;
  }

}
