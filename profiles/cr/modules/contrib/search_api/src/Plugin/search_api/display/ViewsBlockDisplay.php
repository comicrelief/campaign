<?php

namespace Drupal\search_api\Plugin\search_api\display;

use Drupal\search_api\Display\DisplayPluginBase;

/**
 * Represents a Views block display.
 *
 * @SearchApiDisplay(
 *   id = "views_block",
 *   views_display_type = "block",
 *   deriver = "Drupal\search_api\Plugin\search_api\display\ViewsDisplayDeriver"
 * )
 */
class ViewsBlockDisplay extends DisplayPluginBase {

  /**
   * {@inheritdoc}
   */
  public function isRenderedInCurrentRequest() {
    // There is no way to know if a block is embedded on a page, because
    // blocks can be rendered in isolation (see big_pipe, esi, ...). To be
    // sure we're not disclosing information we're not sure about, we always
    // return false.
    return FALSE;
  }

}
