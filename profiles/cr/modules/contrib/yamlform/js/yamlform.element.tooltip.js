/**
 * @file
 * Javascript behaviors for jQuery UI tooltip integration.
 *
 * Please Note:
 * jQuery UI's tooltip implementation is not very responsive or adaptive.
 *
 * @see https://www.drupal.org/node/2207383
 */

(function ($, Drupal) {

  'use strict';

  /**
   * Initialize jQuery UI tooltip support.
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.yamlFormTooltip = {
    attach: function (context) {
      $(context).find('.js-yamlform-element-tooltip').once('yamlform-element-tooltip').each(function () {
        var $element = $(this);
        var $description = $element.children('.description.visually-hidden');

        $element.tooltip({
          items: ':input',
          content: $description.html()
        });
      });
    }
  };

})(jQuery, Drupal);
