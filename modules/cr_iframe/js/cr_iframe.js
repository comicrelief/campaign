/**
 * @file cr_iframe.js
 */

/**
 * IFrame sizer module
 * Sizes an iframe based on requests from child iframe
 */
var iframeSizer = (function () {
  var module = {}, eventMethod;

  /**
   * On module initialisation.
   */
  module.init = function() {
    eventMethod = window.addEventListener ? "addEventListener" : "attachEvent";
    module.sizingListener();
  };

  /**
   * Module sizing listener.
   */
  module.sizingListener = function() {
    window[eventMethod](eventMethod == "attachEvent" ? "onmessage" : "message", function (e) {
      try {
        var json = JSON.parse(e.data);

        if (typeof json.iframe_height !== 'undefined') {

          var iframes = document.getElementsByClassName('iframe-resizable');
          for(var i = 0; i < iframes.length; i++)
          {
            iframes[i].style.height = json.iframe_height + 'px';
            iframes[i].scrolling = 'no';
          }
        }

        if (typeof json.back_to_top !== 'undefined') {
          jQuery('html, body').animate({
            scrollTop: jQuery('.iframe-resizable:first').offset().top + 'px'},'3000');
        }

        if (typeof json.email_address !== 'undefined') {

          console.log('email from iframe: ', json.email_address);

          var domain = window.location.hostname;

          var exdate = new Date();
          exdate.setDate(exdate.getDate() + 365);

          var cookie = [
            'email_address' + '=' + json.email_address,
            'expires=' + exdate.toUTCString(),
            'path=/',
          ];

          if (domain) {
            cookie.push('domain=' + domain);
          }

          document.cookie = cookie.join(';');
        }
      }
      catch(e) {}
    }, false);
  };

  return module;
}());


(function ($) {
  $(document).ready(function () {

    // Initialise the IFrame sizer
    iframeSizer.init();
    
    $('.iframe-block__play').click(function(event){
      if ($('html').hasClass('no-touchevents')) {
        event.preventDefault();
        $(this).closest('.iframe-block__wrapper').addClass('iframe-block__wrapper--on');
        var iframe = $(this).next(".iframe-block__embedded");
        iframe.attr("src", iframe.data("src"));
        $(this).find('.iframe-block__image').hide();
      }
    });
  });
})(jQuery);
