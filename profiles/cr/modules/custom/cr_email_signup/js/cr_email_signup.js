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

    // Not ideal, but form needs reworking otherwise
    $("#edit-send-email").on('touchend mouseup', function(e) {

      $('#cr-email-signup-form').addClass('cr-email-signup--step-2');
      
    });
    }
  };
})(jQuery, Drupal);