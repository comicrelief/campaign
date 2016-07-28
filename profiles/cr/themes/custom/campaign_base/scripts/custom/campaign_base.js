(function($, Drupal) {
  $( document ).ready(function() {
    // Turn our boring select boxes into sexy jQuery UI selectboxes
    $('select').selectmenu({ style:'popup', width: '100%' });


    /* NAV to be module-ised */
    
    // Store height as data-attribute, then set max-height to 0
    $('.menu--main .menu-item--expanded > ul.menu').each( function () {
      $(this)
        .attr("data-menu-height", $(this).height())
          .css("max-height", 0);
    });

    // Click/touch events for 'mobile' menu
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


    /* END OF NAV */

  });
})(jQuery, Drupal);