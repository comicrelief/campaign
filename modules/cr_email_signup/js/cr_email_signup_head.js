/**
 * @file
 */

(function ($) {
  $(document).ready(function () {
    
    $("button.meta-icons__esu-toggle").on("click", function() {
      $(this).toggleClass("active");
      $(".block--cr-email-signup--head").toggleClass("show")
        .find('#edit-email').focus();
    });

    $(".block--cr-email-signup--head .esu-head-close").on("click", function() {
      $("button.meta-icons__esu-toggle").removeClass("active");
      $(".block--cr-email-signup--head").removeClass("show");
    });    
  });
})(jQuery);
