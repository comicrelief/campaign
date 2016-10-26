/**
 * @file
 */

(function ($) {
  $(document).ready(function () {
    $(".play-game").click(function(event){
      console.log('oioi');
      event.preventDefault();
      $('.iframe-block__wrapper').addClass('iframe-block__wrapper--on');
      var iframe = $("#play-iframe");
      iframe.attr("src", iframe.data("src"));
      $('.iframe-block__image').hide();
    });
  });
})(jQuery);
