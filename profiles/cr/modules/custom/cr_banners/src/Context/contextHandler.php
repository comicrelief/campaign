<?php

namespace Drupal\cr_banners\Context;

class contextHandler {
  protected $contexts = [];
  protected $current_context;

  public function __construct() {
    try {
      $config = \Drupal::service('config.factory')->getEditable('cr_banners.contexts');
      $this->contexts = $config->get('contexts');
      $this->current_context = $config->get('current_context');
    }
    catch (\Exception $e) {
      \Drupal::logger('cr_banners')->error(
        "Unable to get contexts configuration. Error: " . $e->getMessage()
      );
    }
  }
  /**
   * Get all contexts stored in configuration.
   */
  public function getContexts(){
    return $this->contexts;
  }
  /**
   * Get current site context.
   */
  public function getCurrentContext(){
    return $this->current_context;
  }

}
