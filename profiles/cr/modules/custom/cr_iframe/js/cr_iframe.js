/**
 * @file
 */

(function ($) {
  $(document).ready(function () {
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
