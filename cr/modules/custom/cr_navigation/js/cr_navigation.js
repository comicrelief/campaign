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

      // Close any active navs when we're toggling on other buttons in the nav
      $('.meta-icons button').on('click', function (e) {
        
        // Remove active class from hamburger nav to collapse it
        $('button.feature-nav-toggle.is-active').removeClass('is-active');
      });

      // Close all overlays and dropdowns when we're clicking on other content
      $(document).on('click', function (e) {

        // Check that we're not interacting with 
        if (!$(e.target).is('.meta-icons *, .feature-nav__icons *, .search-block *, ul.menu *')) {

          // Remove all active state classes from all of our active nav dropdowns
          $('button.feature-nav-toggle.is-active').removeClass('is-active');
          $('#block-campaign-base-main-menu.show, .search-overlay.show, .block--cr-email-signup--head').removeClass('show');
          $('.meta-icons__esu-toggle.active, meta-icons__magnify.active').removeClass('active');
        }
      });
    },

    setUpNav: function (context, settings) {
      var _base = Drupal.behaviors.crNavigation;
      _base.cloneNav();
      _base.handleClick();
    },
  };
})(jQuery);
