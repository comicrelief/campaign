/**
 * @file
 */

(function ($) {
  $(document).ready(function () {
    $(".meta-icons__esu-toggle").on("click", function(event) {
      event.preventDefault();
      $(this).toggleClass("active");

      $(this).attr('aria-pressed', function (i, attr) {
        return attr == 'true' ? 'false' : 'true'
      });

      $(".block--cr-email-signup--head").toggleClass("show")
        .find('#edit-email').focus();
    });

    $(".block--cr-email-signup--head .close-button").on("click", function(e) {
      e.preventDefault();
      $(".meta-icons__esu-toggle").removeClass("active");
      $(".block--cr-email-signup--head").removeClass("show");
    });    
  });
})(jQuery);
