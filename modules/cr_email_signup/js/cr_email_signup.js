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
    firstLoad: false,
    formStep: null,
   },

    attach: function (context, settings) {

      var _base = Drupal.behaviors.crEmailSignUp;
      var _settings = _base.settings;

      // Only run these functions once, as we don't want to add handlers and 
      // scrape browser data every time for each ESU block.
      $(_settings.genericEsuClass).once('crEMailSignup').each(function() {

        if(_settings.firstLoad == false) {
          _base.setDevice(this);
          _base.keyboardSubmit();
          _base.refocus();

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

      // Use this value to set the hidden device field
      Drupal.behaviors.crEmailSignUp.settings.deviceValue = _settings.deviceValue;
 
    },

    setSource: function (context) {

      var _base = Drupal.behaviors.crEmailSignUp;
      var _settings = _base.settings;

      // Check each type of ESU based on the wrapper class used.
      var sourceValue = $(context).hasClass(_settings.esuBannerClass) ? "Banner" : "Header";

      // Use this value to set the hidden source field.
      $(context).find(_settings.hiddenSourceFieldClass).val(sourceValue);

      // Use deviceValue set by setDevice on each form to set the hidden device field.
      $(context).find(_settings.hiddenDeviceFieldClass).val(_settings.deviceValue);
    },

    keyboardSubmit: function () {
      
      var _base = Drupal.behaviors.crEmailSignUp;
      var _settings = _base.settings;

      // Handler to submit form or close ESU header pop-up by pressing enter
      $(".form-submit, .form-text, .esu-head-close", _settings.genericEsuClass).on('keypress', function(event) {

        $eventTarget = $(event.target);
        
        // If the key pressed has the right keycode
        if (event.which == 13) {
          
          event.preventDefault();
          
          // Submit when focused on submit button or close when focused on close button
          if ( $(this).is('.form-submit, .esu-head-close') ) {
            // Use fake mouse event; jQuery form submit function doesn't work here.
            // N.B click doesn't work with jQuery ui menu and mousedown doesn't work with close button 
            $eventTarget.click();
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
    },

    refocus: function () {
       
      var _settings = Drupal.behaviors.crEmailSignUp.settings;

      // Set focus back to input or select menu in case of an error
      // remove invalid email value from input field
      // set focus on close button of head ESU in form's step 2      
      $(document).ajaxComplete(function(event) {
           
        if ( $(event.target.activeElement).closest(".block-cr-email-signup") ) {
          
          if ( $(".block--cr-email-signup--step-2").length ) {
            $block = $(".block--cr-email-signup--step-2");
            // focus on select menu or jquery ui select menu or on close button
            $block.find("select, .ui-selectmenu-button, .esu-head-close").focus();
          }
          if ( $(".block--cr-email-signup--error").length ) {
            $block = $(".block--cr-email-signup--error");
            
            if ( $block.hasClass("error--firstname") ) {
              if( $block.hasClass("error--email") ) {
                // email input field's id isn't reliable and classname is too generic
                $block.find("[name=email]").val('');
              }
              $("#edit-firstname").focus();
            }
            else {
              // email input field's id isn't reliable and classname is too generic
              $input = $block.find("[name=email]");
              $input.val('');
              $input.focus();
            }
          }
          // remove keypress event handlers and re-attach it
          $(".form-submit, .form-text, select", _settings.genericEsuClass).off('keypress');
          $("select, .ui-selectmenu-button", _settings.genericEsuClass).off('selectmenuselect');

          Drupal.behaviors.crEmailSignUp.keyboardSubmit();
        }        
      });
    }, 
  };
})(jQuery);
