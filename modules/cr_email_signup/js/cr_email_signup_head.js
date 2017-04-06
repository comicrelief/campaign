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

    $(".block--cr-email-signup--head .icon").on("click", function() {
      $("button.meta-icons__esu-toggle").removeClass("active");
      $(".block--cr-email-signup--head").removeClass("show");
    });

    // Handler for keypresses for all esu forms
    $(".form-submit").keypress(function(event) {
      // If the key pressed has the right keycode
      if (event.which == 13) {
        // Submit form with fake mouse event; jQuery form submit function doesn't work here!
        $(event.target).mousedown();

        // set focus back to input when error - create function for this!
        $(document).ajaxComplete(function() {
          var blocks = $(".block-cr-email-signup");
          for (i=0; i<blocks.length; i++) {
            var block = blocks[i];
            if ($(block).hasClass("block--cr-email-signup--error")) {
              $(block).find(".form-text").focus();
            }
          }
        });
      }
    });  
  });
})(jQuery);
