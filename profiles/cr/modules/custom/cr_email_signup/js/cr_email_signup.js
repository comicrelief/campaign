(function ($) {

  Drupal.behaviors.crEmailSignUp = {

   settings : {
    formWrapperClass: '.block--cr-email-signup--step-1',
    isTouch: false,
    screenWidth: 0,
    screenWidthLimit: 1200,
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

      if (_settings.isTouch && _settings.screenWidth < _settings.screenWidthLimit) {
        console.log("Probably a touch device", window.screen.width);
        // Do touch-device related things
      } else {
        console.log("Probably not a touch device", window.screen.width);
        // Do non touch-device related things
      }
    },
  };
})(jQuery);
