/**
 * @file cr_iframe.js
 */

/**
 * IFrame sizer module
 * Sizes an iframe based on requests from child iframe
 */
var iframeSizer = (function () {

  var module = {}, eventMethod, eventer, messageEvent;

  /**
   * On module initialisation.
   */
  module.init = function() {
    eventMethod = window.addEventListener ? "addEventListener" : "attachEvent";
    eventer = window[eventMethod];

    messageEvent = eventMethod == "attachEvent" ? "onmessage" : "message";

    module.sizingListener();
  };

  /**
   * Module sizing listener.
   */
  module.sizingListener = function() {
    eventer(messageEvent, function (e) {
      try {
        var json = JSON.parse(e.data);
        if (typeof json.iframe_height !== 'undefned') {
          document.querySelectorAll('.iframe-block iframe').forEach(function(element) {
            element.style.height = json.iframe_height + 'px';
          });
        }
      }
      catch(e) {}
    }, false);
  };

  return module;

}());

// Initialise the IFrame sizer
iframeSizer.init();

(function ($) {
  $(document).ready(function () {
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
