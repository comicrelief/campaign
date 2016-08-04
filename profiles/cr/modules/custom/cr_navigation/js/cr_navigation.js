(function ($) {

  Drupal.behaviors.crNavigation = {
   settings : {
    mainMenuClass: '.menu--main',
    navItemWithSubMenuSelector:'.menu--main > .menu > .menu-item--expanded',
   },

    attach: function (context, settings) {
      var _base = Drupal.behaviors.crNavigation;
      var _settings = _base.settings;

      $(_settings.mainMenuClass).once('crNavigation').each( function(){
        $(this).addClass("crNavigation-processed");
          _base.setUpNav();
      });
    },

    setUpNav: function (context, settings) {
      var _base = Drupal.behaviors.crNavigation;
      var _settings = _base.settings;

      _base.duplicateParentLink();

      _base.toggleMenu();

      /* Setup the Smartmenus plugin with our main menu */
      $('#main-menu').smartmenus({
        subIndicatorsText: "",
        keepHighlighted: false,
        hideOnClick: true,
      });

      /* Bind the 'show' function to also hide all the other submenus */
      $('#main-menu').bind('activate.smapi', function(e, menu) {
        $('#main-menu').smartmenus('menuHideAll');
      });

    },

    /* Updates empty duplicate link (added by template) with the parent item's text and link, dynamically */
    duplicateParentLink: function (context, settings) {

      // Update text and link
      $('.menu--main > .menu > .menu-item--expanded').each (function() {

        $this = $(this);

        // Populate duplicate link with parent link info
        $(this).children('ul.menu')
          .find('.menu-item--duplicate a')
            .attr("href", $this.children('a').attr('href'))
              .find('span').text( $this.children('a').text());
      });
    },

    /* Click event handler to show/hide the mobile nav */
    toggleMenu: function (context, settings) {

      $('button.main-menu-toggle').on('click', function(e) {

        // Change state for visual effect
        $(this).toggleClass('menu-open');

        // Change state of menu itself
        $('#main-menu').toggleClass('menu-open');
      });
    },
  };
})(jQuery);
