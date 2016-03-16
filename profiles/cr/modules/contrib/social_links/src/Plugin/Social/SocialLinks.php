<?php

/**
 * @file
 * Contains \Drupal\social_links\Plugin\Social\SocialLinks.
 */

namespace Drupal\social_links\Plugin\Social;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Url;

/**
 * Plugin implementation of the 'social_links' field type.
 *
 *
 */
class SocialLinks {

  private $social_links = [];
  private $theme_function = 'item_list';

  public function __construct(&$links = [], &$override = false) {
    $links = array_merge([], $links);

    if (!$override && is_array($links)) {
      $links = array_merge($this->getDefaults(), $links);
    }

    $this->registerLinks($links);

  }

  public function registerLinks($links) {
    $module_handler = \Drupal::moduleHandler();
    $hook_links = $module_handler->invokeAll('social_links_alter', [$links]);

    if (!empty($hook_links)) {
      $links = $hook_links;
    }

    $this->addLinks($links);

  }

  public function getDefaults() {
    return [
      'twitter' => [
        'callback' => 'social_links_provider_popup',
        'path' => 'http://twitter.com/home?status=',
      ],
      'facebook' => [
        'callback' => 'social_links_provider_popup',
        'path' => 'https://www.facebook.com/sharer/sharer.php?u=',
      ],
      'googleplus' => [
        'callback' => 'social_links_provider_popup',
        'path' => 'https://plus.google.com/share?url=',
      ],
      'email' => [
        'callback' => 'social_links_provider_email',
        'path' => '',
      ],
    ];
  }

  public function addLinks($links) {
    if (is_array($links)) {
      $this->social_links = array_merge($this->social_links, $links);
    }
  }

  public function renderLinks($entity, &$theme_override = '') {
    return [
      '#items' => $this->getMarkup($entity),
      '#theme' => $this->getTheme($theme_override),
    ];
  }

  public function getMarkup($entity) {
    $markup_array = [];

    $links = $this->getLinks();

    $request = \Drupal::request();
    $route_match = \Drupal::routeMatch();
    $page_title = urlencode(\Drupal::service('title_resolver')->getTitle($request, $route_match->getRouteObject()));
    $entity_url = urlencode($request->getUri());

    // look at nice way to attch js, maybe to theme array
    foreach ($links as $provider => $config) {

      // check in about svg support/ contrib module support

      $link_title = ucfirst($provider);
      $link_class = $provider . '-social-link';
      $link_options = [
        'path' => $config['path'] . $entity_url . '&amp;title=' . $page_title,
        'attributes' => [
          'title' => $link_title,
          'class' => [
            'social-link',
            $link_class,
          ],
        ],
      ];

      if (isset($config['callback'])) {
        $config['callback']($link_options, $entity);
      }

      $url = Url::fromUri($link_options['path']);
      $url->setOptions($link_options);
      $link = \Drupal::l(t($link_title), $url);

      $markup_array[] = $link;
    }

    return $markup_array;
  }

  public function getLinks() {
    return $this->social_links;
  }

  public function getTheme(&$override) {
    if (!empty($override)) {
      $this->theme_function = $override;
      return $override;
    }
    return $this->theme_function;
  }

}
