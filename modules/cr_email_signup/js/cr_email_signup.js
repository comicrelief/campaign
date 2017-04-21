/**
 * @file
 */

(function ($) {

  Drupal.behaviors.crEmailSignUp = {

   settings : {
    genericEsuClass: '.block-cr-email-signup',
    esuBannerClass: 'block--cr-email-signup--banner',
    hiddenDeviceFieldClass: '.esu-device',
    hiddenSourceFieldClass: '.esu-source',
    deviceValue: '',
    sourceValue: '',
    firstLoad: false,
    formStep: null,
    isFirstAjaxCall: true,
   },

    attach: function (context, settings) {

      var _base = Drupal.behaviors.crEmailSignUp;
      var _settings = _base.settings;

      $(_settings.genericEsuClass).once('crEMailSignup').each(function() {

        if(_settings.firstLoad == false) {
          _base.setDevice(this);
          _base.keyboardSubmit();
          Drupal.behaviors.crEmailSignUp.settings.firstLoad = true;
          _settings.firstLoad = Drupal.behaviors.crEmailSignUp.settings.firstLoad;
        }

        _base.setSource(this);

      });
    },

    setDevice: function (context) {

      var _base = Drupal.behaviors.crEmailSignUp;
      var _settings = _base.settings;

      // Construct a string out of the platform.js object attributes.
      _settings.deviceValue = platform.os.family + " " + platform.os.version + " - " + platform.name + " " + platform.version;

      // Replace spaces with underscores.
      _settings.deviceValue = _settings.deviceValue.replace(/\s+/g,"_");

      Drupal.behaviors.crEmailSignUp.settings.deviceValue = _settings.deviceValue;
 
    },

    setSource: function (context) {

      var _base = Drupal.behaviors.crEmailSignUp;
      var _settings = _base.settings;

      // Check each type of ESU based on the wrapper class used.
      if ($(context).hasClass(_settings.esuBannerClass)) {
        _settings.sourceValue = 'Banner';
      } else {
        _settings.sourceValue = 'Header';
      }
      // Use this value to set the hidden source field.
      $(context).find(_settings.hiddenSourceFieldClass).val(_settings.sourceValue);

      // Use deviceValue set by setDevice on each form to set the hidden device field.
      $(context).find(_settings.hiddenDeviceFieldClass).val(_settings.deviceValue);
    },

    keyboardSubmit: function () {
      
      var _base = Drupal.behaviors.crEmailSignUp;
      var _settings = _base.settings;

      // Handler to submit form by pressing enter for all esu forms
      $(".form-submit, .form-text", _settings.genericEsuClass).on('keypress', function(event) {
        
        $eventTarget = $(event.target);
        
        // If the key pressed has the right keycode
        if (event.which == 13) {
          
          // Reset flag for Ajax to run code again
          Drupal.behaviors.crEmailSignUp.settings.isFirstAjaxCall = true;
          
          // Submit when focused on submit button
          if($(this).hasClass('form-submit')) {
            // Submit form with fake mouse event; jQuery form submit function doesn't work here.
            $eventTarget.mousedown();
          }

          // Submit when focused on the input field
          else {
            $parent = $eventTarget.parent('.form-item');
            $form = $parent.parent('form');
            $submit = null; 
            
            // Submit button in different layer in standard ESU form
            if ( $form.attr('id') == 'cr-email-signup-form' ) {
              // Submit this way only for step 1 in form. (Step 2 requires tabbing to button)
              $submit = $form.find(".step1");
            }

            // In all other forms the submit button is a next sibling 
            else {
              $submit = $parent.next(".form-submit");
            }
            
           $submit.mousedown();
          }
        }
      });

      $("select, .ui-selectmenu-button", _settings.genericEsuClass).on('keypress selectmenuselect', function(event, ui) {
        
        // submit selectmenu step in standard ESU form
        if (event.which == 13) {
          if ( $form.attr('id') == 'cr-email-signup-form' ) {
            $submit = $form.find(".step2");
          }
        }
        $submit.mousedown();
      });
        
      $(document).ajaxComplete(function(event) {
        // Set focus back to input or select menu in case of an error
        // Only run code once
        if (Drupal.behaviors.crEmailSignUp.settings.isFirstAjaxCall ) {

          if ( $(".block--cr-email-signup--step-2").length > 0 ) {
            $block = $(".block--cr-email-signup--step-2");
            // focus on select menu or jquery ui select menu
            $block.find("select, .ui-selectmenu-button").focus();
          }

          if ( $(".block--cr-email-signup--error").length > 0 ) {
            $block = $(".block--cr-email-signup--error");

            if ( $block.hasClass("error--firstname") ) {
              $("#edit-firstname").focus();
            }
            else {
              $block.find(".form-text").focus();
            }
          }
          // remove keypress event handlers and re-attach it
          $(".form-submit, .form-text, select", _settings.genericEsuClass).off('keypress');
          $("select, .ui-selectmenu-button", _settings.genericEsuClass).off('selectmenuselect');

          Drupal.behaviors.crEmailSignUp.keyboardSubmit();
        }

        // Prevent the clustered ajax success events from running our code multiple times per form submission
        Drupal.behaviors.crEmailSignUp.settings.isFirstAjaxCall = false;
        
      });
    },
  };
})(jQuery);
