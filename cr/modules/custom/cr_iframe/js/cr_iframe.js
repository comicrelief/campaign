/**
 * @file
 */

(function ($) {
  $(document).ready(function () {
    if ($('.iframe-block').hasClass('iframe-block__load-now') && $('html').hasClass('no-touchevents')) {
      var iframe_load = $(".iframe-block__game");
      $(iframe_load).attr("src", iframe_load.data("src"));
      $('.iframe-block__image').hide();
    }
    $('.iframe-block__play').click(function(event){
      if ($('html').hasClass('no-touchevents')) {
        event.preventDefault();
        $(this).closest('.iframe-block__wrapper').addClass('iframe-block__wrapper--on');
        var iframe = $(this).next(".iframe-block__game");
        iframe.attr("src", iframe.data("src"));
        $(this).find('.iframe-block__image').hide();
      }
    });
  });
})(jQuery);
