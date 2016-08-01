(function ($) {

  Drupal.behaviors.crNavigation = {

   settings : {
    mainMenuClass: '.menu--main',
    navItemWithSubMenuSelector:'.menu--main > .menu > .menu-item--expanded',
    subMenuSelector : '.menu--main .menu-item--expanded > ul.menu',
    touchNavBreakpoint : '(max-width: 1023px)',
    nonTouchNavBreakpoint : '(min-width: 1024px)',
   },

    attach: function (context, settings) {

      var _base = Drupal.behaviors.crNavigation;
      var _settings = _base.settings;

      $(_settings.mainMenuClass).once('crNavigation').each( function(){
        $(this).addClass("crNavigation-processed");
        _base.duplicateParentLink(this);
      });
    },

    duplicateParentLink: function (context, settings) {

      var _base = Drupal.behaviors.crNavigation;
      var _settings = _base.settings;

      // Update text and link
      $( _settings.navItemWithSubMenuSelector ).each (function() {

        $this = $(this);

        // Populate duplicate link with parent link info
        $(this).children('ul.menu')
          .find('.menu-item--duplicate a')
            .attr("href", $this.children('a').attr('href'))
              .find('span').text( $this.children('a').text());
      });

      // Store heights to use in animation
      _base.storeHeights(this);

    },

    storeHeights: function (context, settings) {

      var _base = Drupal.behaviors.crNavigation;
      var _settings = _base.settings;

      // Store height as data-attribute, then set max-height to 0
      $( _settings.subMenuSelector ).each( function () {
        $(this)
          .attr("data-menu-height", $(this).height())
            .css("max-height", 0);
      });

      _base.handleTouch(this);

    },

    handleTouch: function (context, settings) {

      var _base = Drupal.behaviors.crNavigation;
      var _settings = _base.settings;

      $('.menu--main .menu-item--expanded > a').on('click', function(e) {
        
        // TODO: only on mobile menu
        e.preventDefault();

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

      _base.checkBreakpoint(this);

    },

    checkBreakpoint: function (context, settings) {

      var _base = Drupal.behaviors.crNavigation;
      var _settings = _base.settings;

      //console.log ( window.matchMedia('(max-width: 1149px)').matches );

    },
  };
})(jQuery);
