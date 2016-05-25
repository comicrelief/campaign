/**
 * @file
 * Javascript functionality for the ESU strip
 */

(function($, Drupal) {

  'use strict';

  Drupal.behaviors.crEMailSignup = {

    defaults : {
        emailSubmitted: false,
        success: false,
        stepCounter: 1,
        formWrapperClass: '.block--cr-email-signup',
        errorMessageClass: '.messages--error',
        stepClass: 'block--cr-email-signup--step-'
      },

    attach: function (context, settings) {
      var _base = Drupal.behaviors.crEMailSignup;
      var _settings = _base.defaults;

      $(_settings.formWrapperClass).once('crEMailSignup').each( function(){
         $(this).addClass("crEMailSignup-processed " + (_settings.stepClass + _settings.stepCounter) );
          _base.handleSubmit();
      });
    },

    handleSubmit: function (context) {
      var _base = Drupal.behaviors.crEMailSignup;
      var _settings = _base.defaults;

      // Wait until either the error or second-half of the form has been ajax'd in
      $(document).ajaxComplete(function( event, xhr, settings ) {

        // Setup AJAX-ed selectbox with jQuery UI version as this gets missed otherwise
        $('select').selectmenu({ style:'popup', width: '100%' });
        // If we've got errors present, don't do anything
        if ( $(_settings.formWrapperClass).has(_settings.errorMessageClass).length ) {
          // Erroring
        } else {
          // Successful submit
          _base.updateStateClasses();
        }
      });
    },

    updateStateClasses: function () {
      var _base = Drupal.behaviors.crEMailSignup;
      var _settings = _base.defaults;
      // Update classes
      $(_settings.formWrapperClass)
        .removeClass( _settings.stepClass + _settings.stepCounter)
          .addClass( _settings.stepClass + ( _settings.stepCounter +=1 ));
    }
  };
})(jQuery, Drupal);