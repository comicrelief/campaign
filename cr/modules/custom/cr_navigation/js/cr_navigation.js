/**
 * @file
 */

(function ($) {
  Drupal.behaviors.crNavigation = {

    attach: function (context, settings) {
      var _base = Drupal.behaviors.crNavigation;

      $('.header__inner-wrapper nav.navigation').once('crNavigation').each(function () {
        $(this).addClass("crNavigation-processed");
        _base.cloneNav();
      });
    },

    cloneNav: function (context, settings) {

      $( ".header__inner-wrapper nav.navigation > .menu .menu-item:nth-child(1)," +
       ".header__inner-wrapper nav.navigation > .menu .menu-item:nth-child(2), " +
        ".header__inner-wrapper nav.navigation > .menu .menu-item:nth-child(3)")
          .clone().appendTo( ".feature-nav .feature-nav__items" );
    },
  };
})(jQuery);
