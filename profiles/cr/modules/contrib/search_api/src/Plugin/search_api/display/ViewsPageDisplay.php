<?php

namespace Drupal\search_api\Plugin\search_api\display;

use Drupal\search_api\Display\DisplayPluginBase;

/**
 * Represents a Views page display.
 *
 * @SearchApiDisplay(
 *   id = "views_page",
 *   views_display_type = "page",
 *   deriver = "Drupal\search_api\Plugin\search_api\display\ViewsDisplayDeriver"
 * )
 */
class ViewsPageDisplay extends DisplayPluginBase {}
