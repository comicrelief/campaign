/**
 * @file
 */

(function ($) {
  Drupal.behaviors.crNavigation = {

    attach: function (context, settings) {
      var _base = Drupal.behaviors.crNavigation;

      $('.header__inner-wrapper nav.navigation').once('crNavigation').each(function () {
        $(this).addClass("crNavigation-processed");
          _base.setUpNav();
      });
    },

    cloneNav: function (context, settings) {

      $( ".header__inner-wrapper nav.navigation > .menu .menu-item:nth-child(1)," +
       ".header__inner-wrapper nav.navigation > .menu .menu-item:nth-child(2), " +
        ".header__inner-wrapper nav.navigation > .menu .menu-item:nth-child(3)")
          .clone().appendTo( ".feature-nav .feature-nav__items" );
    },

    /* Click event handler trigger our toggle event */
    handleClick: function (context, settings) {

      var _base = Drupal.behaviors.crNavigation;

      // Close any active navs when we're toggling on other buttons in the nav
      $('.meta-icons button').on('click', function (e) {
        
        // Remove active class from hamburger nav to collapse it
        $('button.feature-nav-toggle.is-active').removeClass('is-active');

      });
    },

    setUpNav: function (context, settings) {
      var _base = Drupal.behaviors.crNavigation;
      _base.cloneNav();

      _base.handleClick();

    },

  };
})(jQuery);
