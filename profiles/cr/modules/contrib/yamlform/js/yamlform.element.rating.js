/**
 * @file
 * Javascript behaviors for RateIt integration.
 */

(function ($, Drupal) {

  'use strict';

  /**
   * Initialize rating element using RateIt.
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.yamlFormRating = {
    attach: function (context) {
      $(context)
        .find('[data-rateit-backingfld]')
        .once('yamlform-rating')
        .each(function () {
          var $rateit = $(this);
          var $input = $($rateit.attr('data-rateit-backingfld'));

          // Update the RateIt widget when the input's value has changed.
          // @see yamlform.states.js
          $input.on('change', function () {
            $rateit.rateit('value', $input.val());
          });

          // Set RateIt widget to be readonly when the input is disabled.
          // @see yamlform.states.js
          $input.on('yamlform:disabled', function () {
            $rateit.rateit('readonly', $input.is(':disabled'));
          });
        });
    }
  };

})(jQuery, Drupal);
