(function ($) {

  Drupal.behaviors.crEmailSignUp = {

   settings : {
    formWrapperClass: '.block--cr-email-signup--step-1',
    esuBannerClass: 'block--cr-email-signup--banner',
    hiddenDeviceFieldID: '#esu-device',
    hiddenSourceFieldID: '#esu-source',
    sourceValue: 'Header',
   },

    attach: function (context, settings) {

      var _base = Drupal.behaviors.crEmailSignUp;
      var _settings = _base.settings;

      $(_settings.formWrapperClass).once('crEMailSignup').each( function(){
        $(this).addClass("crEMailSignup-processed");
        _base.setDevice(this);
      });
    },

    setDevice: function (context) {

      var _base = Drupal.behaviors.crEmailSignUp;
      var _settings = _base.settings;

      // Construct a string out of the platform.js object attributes
      _settings.deviceValue = platform.os.family + " " + platform.os.version + " - " + platform.name + " " + platform.version;

      // Replace spaces with underscores
      _settings.deviceValue = _settings.deviceValue.replace(/\s+/g,"_");

      // Use this value to set the hidden device field
      $(_settings.hiddenDeviceFieldID).val(_settings.deviceValue);

      _base.setSource(context);
    },
    
    setSource: function (context) {

      var _base = Drupal.behaviors.crEmailSignUp;
      var _settings = _base.settings;

      // Check the type of ESU based on the wrapper class used in the 
      if ($(context).hasClass(_settings.esuBannerClass)) {
        _settings.sourceValue = 'Banner';
      }

      // Use this value to set the hidden source field
      $(_settings.hiddenSourceFieldID).val(_settings.sourceValue);
    },
  };
})(jQuery);
