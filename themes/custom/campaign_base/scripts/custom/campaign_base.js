(function($, Drupal) {

  $( document ).ready(function() {
    // Turn our boring select boxes into sexy jQuery UI selectboxes
    $('select').selectmenu({ style:'popup', width: '100%' });
    
  });
})(jQuery, Drupal);