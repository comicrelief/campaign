(function ($) {
  $(document).ready(function () {
    $("button.main-menu__icons-esu-toggle").on("click", function() {
      $("header .cr-email-signup-form").toggleClass("show");
    });
  });
})(jQuery);