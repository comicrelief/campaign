/**
 * @file
 * Javascript behaviors for jquery.inputmask integration.
 */

(function ($, Drupal) {

  'use strict';

  /**
   * Initialize input masks.
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.yamlFormElementMask = {
    attach: function (context) {
      $(context).find('input.js-yamlform-element-mask').once('yamlform-element-mask').inputmask();
    }
  };

})(jQuery, Drupal);
