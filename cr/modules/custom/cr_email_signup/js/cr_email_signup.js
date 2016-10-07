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
    sourceValue: 'Header',
   },

    attach: function (context, settings) {

      var _base = Drupal.behaviors.crEmailSignUp;
      var _settings = _base.settings;

      $(_settings.genericEsuClass).once('crEMailSignup').each(function () {
        $(this).addClass("crEMailSignup-processed");
        _base.setDevice(this);
      });
    },

    setDevice: function (context) {

      var _base = Drupal.behaviors.crEmailSignUp;
      var _settings = _base.settings;

      // Construct a string out of the platform.js object attributes.
      _settings.deviceValue = platform.os.family + " " + platform.os.version + " - " + platform.name + " " + platform.version;

      // Replace spaces with underscores.
      _settings.deviceValue = _settings.deviceValue.replace(/\s+/g,"_");

      // Use this value to set the hidden device field.
      $(context).find(_settings.hiddenDeviceFieldClass).val(_settings.deviceValue);

      _base.setSource(context);
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
    },
  };
})(jQuery);
