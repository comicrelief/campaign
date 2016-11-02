/**
 * @file
 */

(function ($) {

  Drupal.behaviors.SocialPopup = {

    settings : {
      popupClass : '.social-link-popup',
      popupWidth : 575,
      popupHeight : 260,
    },

    attach: function (context, settings) {

      var _base = Drupal.behaviors.SocialPopup;
      var _settings = _base.settings;

      $('.social-link-popup').once('SocialPopup').each(function () {
        $(this).addClass("SocialPopup-processed");
      });

      _base.handleClick(this);
    },

    handleClick: function (context) {

      var _base = Drupal.behaviors.SocialPopup;
      var _settings = _base.settings;

      $('.social-link-popup').click(function (event) {

        event.preventDefault();
        var $this = $(this);
        var thisURL = $this.attr('href');
        var thisTitle = $this.attr('title');
        _base.popUpCentre(thisURL, thisTitle, _settings.popupWidth, _settings.popupHeight);
      });
    },

    /** Great dual-screen solution from http://www.xtf.dk/ **/
    popUpCentre: function (url, title, w, h) {

      // Fixes dual-screen position                         Most browsers      Firefox
      var dualScreenLeft = window.screenLeft != undefined ? window.screenLeft : screen.left;
      var dualScreenTop = window.screenTop != undefined ? window.screenTop : screen.top;

      var width = window.innerWidth ? window.innerWidth : document.documentElement.clientWidth ? document.documentElement.clientWidth : screen.width;
      var height = window.innerHeight ? window.innerHeight : document.documentElement.clientHeight ? document.documentElement.clientHeight : screen.height;

      var left = ((width / 2) - (w / 2)) + dualScreenLeft;
      var top = ((height / 2) - (h / 2)) + dualScreenTop;
      var newWindow = window.open(url, title, 'scrollbars=yes, width=' + w + ', height=' + h + ', top=' + top + ', left=' + left);

      // Puts focus on the newWindow
      if (window.focus) {
          newWindow.focus();
      }
    }
  };
})(jQuery);
