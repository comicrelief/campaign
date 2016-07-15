(function($, Drupal) {
  $( document ).ready(function() {
    // Turn our boring select boxes into sexy jQuery UI selectboxes
    $('select').selectmenu({ style:'popup', width: '100%' });



    // TODO: implemented as Drupal behaviour within navigation module
    $('.menu--main .menu-item--expanded > a').on('click', function(e){

      // Prevent our parent item from acting as link, user
      // able to link to parent content with duplicate link within the ul (hook to be developed)
      e.preventDefault();

      // Close any open nav items
      $('.menu--main .menu-item--expanded > ul.menu').removeClass('active');

      // Active the selected menu
      $(this).next('ul.menu').addClass('active');
    });

  });
})(jQuery, Drupal);
