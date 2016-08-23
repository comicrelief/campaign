(function($, Drupal) {

  $( document ).ready(function() {
    // Turn our boring select boxes into sexy jQuery UI selectboxes
    $('select').selectmenu({ style:'popup', width: '100%' });
    // Activate lighcase
    $('a[rel^=lightcase]').lightcase();
	  
	  newPosition();
	  newHeight();
	  
  });

  sSize = $(window).width();

      	
  $(window).resize(function() {          
    sSize = $(window).width();
    if (/*sSize >= 768 || sSize == 992 ||*/ sSize == 1400) {
      newPosition();
      newHeight();
    }
    if (sSize >= 1400) {
      $('.media-video').closest('.cw-article').addClass('cw-article-media-video');
    }
  });

  var newHeight = function() {
    $('.cw-article__body--hide-show').each(function(){
      var aHeight = $(this).height();
      var hHeight = $(this).find('.cw-article__title').height();
      var nHeight = aHeight - hHeight + 5;
      $(this).parent().find('.cw-article__image').css('height',nHeight+'px');
    });
  }

  var newPosition = function() {
    $('.cw-article__body--hide-show').each(function(){
      var aHeight = $(this).height();
      var hHeight = $(this).find('.cw-article__title').height();
      var topPos = aHeight - hHeight;
      if (topPos > 167 && sSize < 992) {
        $(this).css('top','167px');
      } else if (topPos > 229 && sSize < 1400) {
        $(this).css('top','229px');
      } else if (topPos > 335) {
        $(this).css('top','335px');
      } else {
        $(this).css('top',topPos+'px');
      }
    });
  }

      
  // Content wall body top position 
  // Drupal.behaviors.contentWall = {
  //   attach: function (context, settings) {
	// 		$('.cw-article__body--hide-show').once('cw-hide-show', function () {
	//      });   
  //   }
  // };
})(jQuery, Drupal);
