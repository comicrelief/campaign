/**
 * @file
 * Javascript behaviors for toggle integration.
 */

(function ($, Drupal) {

  'use strict';

  /**
   * Initialize toggle element using Toggles.
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.yamlFormToggle = {
    attach: function (context) {
      $(context).find('.js-yamlform-toggle').once('yamlform-toggle').each(function () {
        var $toggle = $(this);
        var $wrapper = $toggle.parent();
        var $checkbox = $wrapper.find('input[type="checkbox"]');
        var $label = $wrapper.find('label');

        $toggle.toggles({
          checkbox: $checkbox,
          clicker: $label,
          text: {
            on: $toggle.attr('data-toggle-text-on') || '',
            off: $toggle.attr('data-toggle-text-off') || ''
          }
        });

        // If checkbox is disabled then add the .disabled class to the toggle.
        if ($checkbox.attr('disabled') || $checkbox.attr('readonly')) {
          $toggle.addClass('disabled');
        }

        // Add .clearfix to the wrapper.
        $wrapper.addClass('clearfix')

      });
    }
  };

  // Track the disabling of a toggle's checkbox using states.
  $(document).on('state:disabled', function (event) {
    $('.js-yamlform-toggle').each(function () {
      var $toggle = $(this);
      var $wrapper = $toggle.parent();
      var $checkbox = $wrapper.find('input[type="checkbox"]');
      var isDisabled = ($checkbox.attr('disabled') || $checkbox.attr('readonly'));
      (isDisabled) ? $toggle.addClass('disabled') : $toggle.removeClass('disabled');
    });
  });

})(jQuery, Drupal);
