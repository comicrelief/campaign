<?php

namespace Drupal\search_api\Plugin\search_api\display;

use Drupal\search_api\Display\DisplayPluginBase;

/**
 * Represents a Views REST display.
 *
 * @SearchApiDisplay(
 *   id = "views_rest",
 *   views_display_type = "rest_export",
 *   deriver = "Drupal\search_api\Plugin\search_api\display\ViewsDisplayDeriver"
 * )
 */
class ViewsRestDisplay extends DisplayPluginBase {}
