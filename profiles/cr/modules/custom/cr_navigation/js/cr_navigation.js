(function($, Drupal) {

  $( document ).ready(function() {


    /* NAV to be module-ised */
    $('.menu--main > .menu > .menu-item--expanded').each (function() {

      // Cache everything
      $this_expanded_menu_item = $(this);
      $this_parent_link = $this_expanded_menu_item.children('a');
      $this_submenu = $(this).children('ul.menu');

      // Store parent link stuff
      var this_link = $this_parent_link.attr('href');
      var this_text =  $this_parent_link.text();


      // Populate duplicate link with parent link info
      $this_submenu
        .find('.menu-item--duplicate a')
          .attr("href", this_link)
            .find('span').text(this_text);
    });

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
  });
})(jQuery, Drupal);