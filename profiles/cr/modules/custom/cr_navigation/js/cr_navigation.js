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

      _base.toggleMenu();

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

    /* Click event handler to show/hide the mobile nav */
    toggleMenu: function (context, settings) {

      $('button.main-menu-toggle').on('click', function (e) {

        // Change state for visual effect.
        $(this).toggleClass('is-active');

        // Change state of menu itself.
        $('#main-menu, #block-kidsmenu > .menu').toggleClass('menu-open');
      });
    },
  };
})(jQuery);
