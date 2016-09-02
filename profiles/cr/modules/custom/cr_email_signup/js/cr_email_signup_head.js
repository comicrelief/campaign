(function ($) {
  $(document).ready(function () {
    $("button.main-menu__icons-esu-toggle").on("click", function() {
      $(".block--cr-email-signup--head").toggleClass("show");
    });
    $(".block--cr-email-signup--head .icon").on("click", function() {
      $(".block--cr-email-signup--head").removeClass("show");
    });
  });
})(jQuery);