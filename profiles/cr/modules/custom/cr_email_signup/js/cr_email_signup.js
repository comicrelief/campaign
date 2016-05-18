/**
 * @file
 * Javascript functionality for the ESU strip
 */

(function($, Drupal) {
  'use strict';

  Drupal.behaviors.crEMailSignup = {

    attach: function (context, settings) {
      var _base = Drupal.behaviors.crEMailSignup;
      $('#cr-email-signup-form', context).once('crEMailSignup', _base.handleEmailSubmit()).addClass('processed');
    },

    handleEmailSubmit: function (context) {
      $(document).on('click', '#edit-send-email', function(){
        alert("success");
      });
    }
  };
})(jQuery, Drupal);