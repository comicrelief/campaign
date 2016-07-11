(function ($) {

  Drupal.behaviors.crEmailSignUp = {

   settings : {
    formWrapperClass: '.block--cr-email-signup--step-1',
    isTouch: false,
    screenWidth: 0,
    screenWidthLimit: 1200,
    hiddenDeviceField: 'device',
    hiddenSourceField: 'source',
    deviceValue: 'DESKTOP',
   },

    attach: function (context, settings) {

      var _base = Drupal.behaviors.crEmailSignUp;
      var _settings = _base.settings;

      $(_settings.formWrapperClass).once('crEMailSignup').each( function(){
        $(this).addClass("crEMailSignup-processed");
        _base.checkTouchAndWidth();
      });
    },

    checkTouchAndWidth: function () {

      var _base = Drupal.behaviors.crEmailSignUp;
      var _settings = _base.settings;

      // Check for our Modernizr-added class to denote a touch device
      _settings.isTouch = $('html').hasClass('touchevents');

      // Check the size of the device screen itself, rather than the browser window
      _settings.screenWidth = window.screen.width;

      //Update our default value if we're using a touch device below our screen width limit
      if (_settings.isTouch && _settings.screenWidth < _settings.screenWidthLimit) {
        _settings.deviceValue = "MOBILE";
      }

      // Use this value to set the hidden field
      $('input[name='+_settings.hiddenDeviceField+'').val(_settings.deviceValue);
    },
  };
})(jQuery);
