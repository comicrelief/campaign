/**
 * @file
 */

(function ($) {
  $(document).ready(function () {
    $(".meta-icons__esu-toggle, .js-esu-popup").on("click", function(event) {
      event.preventDefault();
      $(this).toggleClass("active");

      $(this).attr('aria-pressed', function (i, attr) {
        return attr == 'true' ? 'false' : 'true'
      });

      $(".block--cr-email-signup--head").toggleClass("visible").find(".form-item-email input").focus();
      $("a[role=button].meta-icons__magnify").removeClass("active");
      $(".search-block, header[role='banner'] nav, .search-overlay").removeClass("show");
    });

    $(".block--cr-email-signup--head .close-button").on("click", function(e) {
      e.preventDefault();
      $(".meta-icons__esu-toggle, .js-esu-popup").removeClass("active");
      $(".block--cr-email-signup--head").removeClass("visible");
    });

  });
})(jQuery);
