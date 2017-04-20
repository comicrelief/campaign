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
   },

    attach: function (context, settings) {

      var _base = Drupal.behaviors.crEmailSignUp;
      var _settings = _base.settings;

      $(_settings.genericEsuClass).once('crEMailSignup').each(function() {

        if(_settings.firstLoad == false) {
          _base.setDevice(this);
          _base.keyboardSubmit(context);
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

    keyboardSubmit: function (context, settings) {
      
      var _base = Drupal.behaviors.crEmailSignUp;
      var _settings = _base.settings;

      // Handler to submit form by pressing enter on button for all esu forms
      $(".form-submit, .form-text", _settings.genericEsuClass).keypress(function(event) {
        $eventTarget = $(event.target);
        // If the key pressed has the right keycode
        if (event.which == 13) {
          // Submit form with fake mouse event; jQuery form submit function doesn't work here!
          if($(this).hasClass('form-submit')) {
            $eventTarget.mousedown();
          }
          else {
            $parent = $eventTarget.parent('.form-item');
            $form = $parent.parent('form');
            $submit = null; 
            // Different layer in case of standard ESU form
            if ( $form.attr('id') == 'cr-email-signup-form' ) {
              // Submit this way only for step 1 in form. (Step 2 requires tabbing to button)
              $submit = $form.find(".step1");
            }
            else {
             $submit = $parent.next(".form-submit");
            }
            $submit.mousedown();
          }
          refocus();
        }
      });

      function refocus() {
        // Set focus back to input or select menu
        $(document).ajaxComplete(function() {
          if ( $(".block--cr-email-signup--error") ) {
            $block = $(".block--cr-email-signup--error");
            if ( $block.hasClass("error--firstname") ) {
              $("#edit-firstname").focus();
            }
            else {
              $block.find(".form-text").focus();
            }
          }
          if ( $(".block--cr-email-signup--step-2") ) {
            $block = $(".block--cr-email-signup--step-2");
            $block.find("select, .ui-selectmenu-button").focus();
          }
        });
      }
    },
  };
})(jQuery);
