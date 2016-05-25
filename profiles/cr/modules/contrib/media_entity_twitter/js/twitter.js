(function ($, Drupal) {
  "use strict";

  Drupal.behaviors.twitterMediaEntity = {
    attach: function (context) {
      twttr.widgets.load();
    }
  };

})(jQuery, Drupal);
