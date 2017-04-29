<?php

/**
 * @file
 * Contains \Drupal\amp\Theme\AmpNegotiator.
 */

namespace Drupal\amp\Theme;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\amp\Routing\AmpContext;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Theme\ThemeNegotiatorInterface;

/**
 * Sets the active theme on amp pages.
 */
class AmpNegotiator implements ThemeNegotiatorInterface {

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The route amp context to determine whether a route is an amp one.
   *
   * @var \Drupal\amp\Routing\AmpContext
   */
  protected $ampContext;

  /**
   * Creates a new AmpNegotiator instance.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\amp\Routing\AmpContext $amp_context
   *   The route amp context to determine whether the route is an amp one.
   */
  public function __construct(ConfigFactoryInterface $config_factory, AmpContext $amp_context) {
    $this->configFactory = $config_factory;
    $this->ampContext = $amp_context;
  }

  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $route_match) {
    return $this->ampContext->isAmpRoute($route_match->getRouteObject());
  }

  /**
   * {@inheritdoc}
   */
  public function determineActiveTheme(RouteMatchInterface $route_match) {
    return $this->configFactory->get('amp.theme')->get('amptheme');
  }

}
