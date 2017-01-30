/**
 * @file
 */

(function ($) {

  Drupal.behaviors.crNavigation = {

    attach: function (context, settings) {
      var _base = Drupal.behaviors.crNavigation;

      $('.menu--main, .menu--kids-menu').once('crNavigation').each(function () {
        $(this).addClass("crNavigation-processed");
          _base.setUpNav();
      });
    },

    cloneNav: function (context, settings) {
      var _base = Drupal.behaviors.crNavigation;
      $( ".menu--main .menu--level-0 .menu-item:nth-child(1), .menu--main .menu--level-0 .menu-item:nth-child(2), .menu--main .menu--level-0 .menu-item:nth-child(3)" ).clone().appendTo( ".meta-nav .meta-nav__feature-items" );
    },

    /* Click event handler trigger our toggle event */
    handleClick: function (context, settings) {

      var _base = Drupal.behaviors.crNavigation;

      // Close any active navs when we're toggling on other buttons in the nav
      $('.meta-nav__icons button:not(.meta-nav-toggle)').on('click', function (e) {
        
        // Remove active class from hamburger nav to collapse it
        $('button.meta-nav-toggle.is-active').removeClass('is-active');

        // Remove active class from kids menu
        $('#main-menu, #block-kidsmenu > .menu.menu-open').removeClass('menu-open');
      });
    },

    setUpNav: function (context, settings) {
      var _base = Drupal.behaviors.crNavigation;

      _base.cloneNav();

      _base.handleClick();

    },

  };
})(jQuery);
