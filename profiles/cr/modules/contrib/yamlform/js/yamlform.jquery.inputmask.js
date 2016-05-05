/**
 * @file
 * Javascript behaviors for YAML form jquery.inputmask integration.
 */

(function ($, Drupal) {

  'use strict';

  Drupal.behaviors.yamlFormInputMask = {
    attach: function (context) {
      $(context).find('input.js-yamlform-input-mask').once('yamlform-input-mask').inputmask();
    }
  };

})(jQuery, Drupal);
