/**
 * @file
 * Javascript behaviors for jQuery Word and Counter Counter integration.
 */

(function ($, Drupal) {

  'use strict';

  /**
   * Initialize text field and textarea word and character counter.
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.yamlFormCounter = {
    attach: function (context) {
      $(context).find('.js-yamlform-counter').once().each(function () {
        var options = {
          goal: $(this).attr('data-counter-limit'),
          msg: $(this).attr('data-counter-message')
        };
        // Only word type can be defined, otherwise the counter defaults to
        // character counting.
        if ($(this).attr('data-counter-type') == 'word') {
          options.type = 'word';
        }
        $(this).counter(options);
      })

    }
  };

})(jQuery, Drupal);
