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

    // Handler to submit form by pressing enter on button for all esu forms
    $(".form-submit").keypress(function(event) {
      // If the key pressed has the right keycode
      if (event.which == 13) {
        // Submit form with fake mouse event; jQuery form submit function doesn't work here!
        $(event.target).mousedown();
        refocus();
      }
    });
    // Handler to sumbmit form by pressing enter in input field
    $(".form-text").keypress(function(event) {
      if (event.which == 13) {
        var parent = $(event.target).parent();
        // Different layer in case of standard ESU form
        if ( $(parent).parent().attr('id') == 'cr-email-signup-form' ) {
          // Submit this way only for step 1 in form. (Step 2 requires tabbing to button)
          var submit = $(parent).parent().find(".step1");
        }
        else {
          var submit = $(parent).next(".form-submit");
        }
        $(submit).mousedown();
        refocus();
      }
    });

    function refocus() {
      // Set focus back to input or select menu
      $(document).ajaxComplete(function() {
        var blocks = $(".block-cr-email-signup");
        for ( i=0 ; i<blocks.length; i++ ) {
          var block = blocks[i];
          if ( $(block).hasClass("block--cr-email-signup--error") ) {
            if ( $(block).hasClass("error--firstname") ) {
              $("#edit-firstname").focus();
            }
            else {
              $(block).find(".form-text").focus();
            }
          }
          if ( $(block).hasClass("block--cr-email-signup--step-2") ) {
            $(block).find(".ui-selectmenu-button").focus();
          }
        }
      });
    }
    
  });
})(jQuery);
