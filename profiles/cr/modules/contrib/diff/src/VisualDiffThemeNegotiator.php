<?php

namespace Drupal\diff;

use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\user\Theme\AdminNegotiator;

/**
 * Visual inline layout theme negotiator.
 *
 * @package Drupal\diff
 */
class VisualDiffThemeNegotiator extends AdminNegotiator {

  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $routeMatch) {
    if ($routeMatch->getParameter('filter') === 'visual_inline') {
      if ($this->isDiffRoute($routeMatch)) {
        if ($this->configFactory->get('diff.settings')->get('general_settings.visual_inline_theme') === 'default') {
          return TRUE;
        }
      }
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function determineActiveTheme(RouteMatchInterface $route_match) {
    return $this->configFactory->get('system.theme')->get('default');
  }

  /**
   * Checks if route names for node or other entity are corresponding.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   Route match object.
   *
   * @return bool
   *   Return TRUE if route name is ok.
   */
  public function isDiffRoute(RouteMatchInterface $route_match) {
    $regex_pattern = '/^entity\..*\.revisions_diff$/';
    return $route_match->getRouteName() === 'diff.revisions_diff' ||
      preg_match($regex_pattern, $route_match->getRouteName());
  }
}
