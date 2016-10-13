/**
 * @file
 * Javascript behaviors for signature pad integration.
 */

(function ($, Drupal) {

  'use strict';

  /**
   * Initialize signature element.
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.yamlFormSignature = {
    attach: function (context) {
      $(context).find('input.js-yamlform-signature').once('yamlform-signature').each(function () {
        var $input = $(this);
        var value = $input.val();
        var $wrapper = $input.parent();
        var $canvas = $wrapper.find('canvas');
        var $button = $wrapper.find('input[type="submit"]');
        var canvas = $canvas[0];

        // Set height.
        $canvas.attr('width', $wrapper.width());
        $canvas.attr('height', $wrapper.width()/3);
        $(window).resize(function () {
          $canvas.attr('width', $wrapper.width());
          $canvas.attr('height', $wrapper.width()/3);

          // Resizing clears the canvas so we need to reset the signature pad.
          signaturePad.clear();
          var value = $input.val();
          if (value) {
            signaturePad.fromDataURL(value);
          }
        });

        // Initialize signature canvas.
        var signaturePad = new SignaturePad(canvas, {
          'onEnd': function () {
            $input.val(signaturePad.toDataURL());
          }
        });

        // Set value.
        if (value) {
          signaturePad.fromDataURL(value);
        }

        // Set reset handler.
        $button.on('click', function () {
          signaturePad.clear();
          $input.val();
          this.blur();
          return false;
        });

        // Input onchange clears signature pad if value is empty.
        // @see yamlform.states.js
        $input.on('change', function () {
          if (!$input.val()) {
            signaturePad.clear();
          }
        });

        // Turn signature pad off/on when the input is disabled/enabled.
        // @see yamlform.states.js
        $input.on('yamlform:disabled', function () {
          if ($input.is(':disabled')) {
            signaturePad.off();
          }
          else {
            signaturePad.on();
          }
        });

      });
    }
  };

})(jQuery, Drupal);
