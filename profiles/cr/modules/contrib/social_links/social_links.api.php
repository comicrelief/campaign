<?php

/**
 * @file
 * API documentation for Social Links.
 */

/**
 * Define social link providers and alter defaults.
 *
 * @return array
 */
function hook_social_links_alter($links) {
  // Add a linkedin provider, using the default popup callback.
  $links['linkedin'] = [
    'callback' => 'social_links_provider_popup',
    'path' => 'http://www.linkedin.com/shareArticle?mini=true&url=',
  ];

  return $links;
}
