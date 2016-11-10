(function($, Drupal) {

  $( document ).ready(function() {
    // Turn our boring select boxes into sexy jQuery UI selectboxes
    $('select').selectmenu({ style:'popup', width: '100%' });
    // Activate lighcase
    // Video lightcase
    $('a[data-rel^=lightcase]').lightcase({
      iframe: {
        width: "100%",
        height: "100%",
        frameborder: 0
      },
      onFinish : {
        custom: function() {
          var caption = $(this).parent().find('.media-block__caption');
          if (caption.length) {
            lightcase.get('caption').html(caption.html());
            $('#lightcase-caption').show();
            }
            lightcase.resize();
          }
        }
    });

    // Search hold on
    // $("button.main-menu__icons-magnify").on("click", function() {
    //   $(this).toggleClass("active");
    //   $(".search-block").toggleClass("show");
    // });
    // $(".search-block:not").on("click", function() {
    //   $("button.main-menu__icons-magnify").removeClass("active");
    //   $(".search-block").removeClass("show");
    // });
	  
	  newPosition();
	  newHeight();

  });
    // reload our styling for select boxes after ajax
  $( document ).ajaxComplete(function() {
    $('select').selectmenu({ style:'popup', width: '100%' });
  });


  sSize = $(window).width();

      	
  $(window).resize(function() {          
    sSize = $(window).width();
    if (sSize >= 740 || sSize == 1024) {
      newPosition();
      newHeight();
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
      if (topPos > 245 && sSize < 1024) {
        $(this).css('top','245px');
      } else if (topPos > 229 && sSize < 1024) {
        $(this).css('top','229px');
      } else if (topPos > 335) {
        $(this).css('top','316px');
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
