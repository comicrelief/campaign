/**
 * @file
 * Javascript behaviors for YAML form.
 */

(function ($, Drupal) {

  'use strict';

  Drupal.behaviors.yamlFormDraft = {
    attach: function (context) {
      $(context).find('#edit-draft').once().on('click', function() {
        $(this.form).attr('novalidate', 'novalidate');
      });
    }
  };

})(jQuery, Drupal);
