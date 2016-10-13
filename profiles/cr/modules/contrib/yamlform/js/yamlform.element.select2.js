/**
 * @file
 * Javascript behaviors for Select2 integration.
 */

(function ($, Drupal) {

  'use strict';

  /**
   * Initialize Select2 support.
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.yamlFormSelect2 = {
    attach: function (context) {
      $(context)
        .find('select.js-yamlform-select2, .js-yamlform-select2 select')
        .once('yamlform-select2')
        // http://stackoverflow.com/questions/14313001/select2-not-calculating-resolved-width-correctly-if-select-is-hidden
        .css('width', '100%')
        .select2();
    }
  };

})(jQuery, Drupal);
