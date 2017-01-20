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

    setUpNav: function (context, settings) {
      var _base = Drupal.behaviors.crNavigation;

      $('#main-menu .menu-item a, #block-kidsmenu > .menu .menu-item a').wrapInner('<span class="menu-item__text"></span>');

      _base.duplicateParentLink();

      _base.handleClick();

      /* Setup the Smartmenus plugin with our main menu */
      $('#main-menu, #block-kidsmenu > .menu').smartmenus({
        subIndicatorsText: "",
        keepHighlighted: false,
        hideOnClick: true,
      });

      /* Bind the 'show' function to also hide all the other submenus */
      $('#main-menu').bind('activate.smapi', function (e, menu) {
        $('#main-menu').smartmenus('menuHideAll');
      });

      $('#block-kidsmenu > .menu').bind('activate.smapi', function (e, menu) {
        $('#block-kidsmenu > .menu').smartmenus('menuHideAll');
      });

    },

    /* Updates empty duplicate link (added by template) with the parent item's text and link, dynamically */
    duplicateParentLink: function (context, settings) {

      /* Update text and link */
      $('.menu--main > .menu > .menu-item--expanded, .menu--kids-menu > .menu > .menu-item--expanded').each(function () {

        $this = $(this);

        // Populate duplicate link with parent link info.
        $(this).children('ul.menu')
          .find('.menu-item--duplicate a')
            .attr("href", $this.children('a').attr('href'))
              .find('span').text($this.children('a').text());
      });
    },

    /* Click event handler trigger our toggle event */
    handleClick: function (context, settings) {

      var _base = Drupal.behaviors.crNavigation;
      
      $('button.main-menu-toggle').on('click', function (e) {
        _base.toggleMenu();
      });

      // Close any active navs when we're toggling on other buttons in the nav
      $('.main-menu__icons button:not(.main-menu-toggle)').on('click', function (e) {
        
        // Remove active class from hamburger nav to collapse it
        $('button.main-menu-toggle.is-active').removeClass('is-active');

        // Remove active class from kids menu
        $('#main-menu, #block-kidsmenu > .menu.menu-open').removeClass('menu-open');
      });
    },

    /* Update the main menu based on state, and hide other nav item dropdowns where appropriate */
    toggleMenu: function (context, settings) {

      $thisMenuButton = $('button.main-menu-toggle');
      
      // Change state for visual effect.
      $thisMenuButton.toggleClass('is-active');

      // Change state of menu itself.
      $('#main-menu, #block-kidsmenu > .menu').toggleClass('menu-open');

      // If we've just activated our main menu, remove all active/show states from other nav dropdowns
      if ( $thisMenuButton.hasClass('is-active')) {
        $('.main-menu__icons button:not(.main-menu-toggle)').removeClass('active');
        $('#block-emailsignupblockhead, .search-overlay').removeClass('show');
      }
    },
  };
})(jQuery);
