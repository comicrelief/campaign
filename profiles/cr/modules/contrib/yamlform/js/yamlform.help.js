/**
 * @file
 * Javascript behaviors for help.
 */

(function ($, Drupal) {

  'use strict';

  /**
   * Handles disabling help dialog for mobile devices.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Attaches the behavior for disabling help dialog for mobile devices.
   */
  Drupal.behaviors.yamlFormHelpDialog = {
    attach: function (context) {
      $(context).find('.button-yamlform-play').once().on('click', function(event) {
        if ($(window).width() < 768) {
          event.stopImmediatePropagation();
        }
      }).each(function() {
        // Must make sure that this click event handler is execute first and
        // before the AJAX dialog handler.
        // @see http://stackoverflow.com/questions/2360655/jquery-event-handlers-always-execute-in-order-they-were-bound-any-way-around-t
        var handlers = $._data(this, 'events')['click'];
        var handler = handlers.pop();
        // move it at the beginning
        handlers.splice(0, 0, handler);
      });
    }
  };

})(jQuery, Drupal);
