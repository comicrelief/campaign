(function ($) {

  Drupal.behaviors.crNavigation = {

   settings : {
    mainMenuClass: '.menu--main',
    navItemWithSubMenuSelector:'.menu--main > .menu > .menu-item--expanded',

    subMenuSelector : '.menu--main .menu-item--expanded > ul.menu',
    touchNavBreakpoint : '(max-width: 1149px)',
    noneTouchNavBreakpoint : '(min-width: 1150px)',
    isTouchDevice : false,
    isTouchNav : false,
   },

    attach: function (context, settings) {
      var _base = Drupal.behaviors.crNavigation;
      var _settings = _base.settings;

      $(_settings.mainMenuClass).once('crNavigation').each( function(){
        $(this).addClass("crNavigation-processed");

          _base.duplicateParentLink();
          
          $('#main-menu').smartmenus({
            subIndicatorsText: "",
            keepHighlighted: false
          });

        _base.toggleMenu();

      });
    },

    duplicateParentLink: function (context, settings) {
      var _base = Drupal.behaviors.crNavigation;
      var _settings = _base.settings;

      // Update text and link
      $( _settings.navItemWithSubMenuSelector ).each (function() {

        $this = $(this);

        // Add this class so the SmartMenu plugin ignores any clicks, making it function as a button only
        $this.children('a').addClass('disabled');

        // Populate duplicate link with parent link info
        $(this).children('ul.menu')
          .find('.menu-item--duplicate a')
            .attr("href", $this.children('a').attr('href'))
              .find('span').text( $this.children('a').text());
      });
    },

    toggleMenu: function (context, settings) {

      var _base = Drupal.behaviors.crNavigation;
      var _settings = _base.settings;

      $('button.main-menu-toggle').on('click', function(e) {

        // Change state for visual effect
        $(this).toggleClass('menu-open');

        // Change state of menu itself
        $('#main-menu').toggleClass('menu-open');
      });
    },





    /*init : function () {
      var _base = Drupal.behaviors.crNavigation;
      var _settings = _base.settings;

      // Check Modernizr class to assess if we're using a touch device or not
      _settings.isTouchDevice = $('html').hasClass('touchevents');
      _base.checkTouchBreakpoint();

      // Runs regardless of breakpoint/touch
      _base.duplicateParentLink(this);
      _base.storeHeights(this);

    },




    storeHeights: function (context, settings) {
      var _base = Drupal.behaviors.crNavigation;
      var _settings = _base.settings;

      // If we're on the non touch breakpoint, we don't need to do the JS magic for animating, so exit the function
      if ( _settings.isTouchNav ) {
        return;
      }

      // Store height as data-attribute for animating with later, then set max-height to 0
      $(_settings.subMenuSelector).each( function () {
        $(this)
          .attr("data-menu-height", $(this).height())
            .css("max-height", 0);
      });
      _base.animateMenu(this);
    },


    animateMenu: function (context, settings) {

      var _base = Drupal.behaviors.crNavigation;
      var _settings = _base.settings;

      $('.menu--main .menu-item--expanded > a').on('click', function(e) {

        // Only prevent default when we're on the mobile/touch menu, else exit the function
        if ( _settings.isTouchNav ) {
          e.preventDefault();
        } else {
          return;
        }
      
        $thisMenu = $(this).next('ul.menu');

        // Set our max-height dynamically based on actual height stored earlier
        var thisMenuHeight =  $thisMenu.attr("data-menu-height");
        // Current height of menu
        var thisCurrentHeight = $thisMenu.height();

        // Update current height to toggle between the 2 values
        thisCurrentHeight = thisMenuHeight == thisCurrentHeight ? 0 : thisMenuHeight;

        $thisMenu.css("max-height", thisCurrentHeight + "px").toggleClass('active');

        // Close any open nav items
        $('.menu--main .menu-item--expanded > a')
          .not(this)
            .next('ul.menu')
              .removeClass('active')
                .css("max-height", 0);
      });
    },

    checkTouchBreakpoint : function () {

      var _settings = Drupal.behaviors.crNavigation.settings;

      if ( window.matchMedia('(max-width: 1149px)').matches ) {
        _settings.isTouchNav = true;
      } else {
        _settings.isTouchNav = false;
      }
    },*/
  };
})(jQuery);
