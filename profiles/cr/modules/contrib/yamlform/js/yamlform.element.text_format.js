/**
 * @file
 * Javascript behaviors for Text format integration.
 */

(function ($, Drupal) {

  'use strict';

  /**
   * Enhance text format element.
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.yamlFormTextFormat = {
    attach: function (context) {
      $(context).find('.js-text-format-wrapper textarea').once().each(function () {
        var $textarea = $(this);
        if (!window.CKEDITOR) {
          return;
        }

        // Update the CKEDITOR when the textarea's value has changed.
        // @see yamlform.states.js
        $textarea.on('change', function () {
          if (CKEDITOR.instances[$textarea.attr('id')]) {
            var editor = CKEDITOR.instances[$textarea.attr('id')];
            editor.setData($textarea.val());
          }
        });

        // Set CKEDITOR to be readonly when the textarea is disabled.
        // @see yamlform.states.js
        $textarea.on('yamlform:disabled', function () {
          if (CKEDITOR.instances[$textarea.attr('id')]) {
            var editor = CKEDITOR.instances[$textarea.attr('id')];
            editor.setReadOnly($textarea.is(':disabled'));
          }
        });

      });
    }
  };

})(jQuery, Drupal);
