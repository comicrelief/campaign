/**
 * @file
 */

(function ($) {
  $(document).ready(function () {
    $("button.meta-nav__icons-esu-toggle").on("click", function() {
      $(this).toggleClass("active");
      $(".block--cr-email-signup--head").toggleClass("show");
    });
    $(".block--cr-email-signup--head .icon").on("click", function() {
      $("button.meta-nav__icons-esu-toggle").removeClass("active");
      $(".block--cr-email-signup--head").removeClass("show");
    });
  });
})(jQuery);
