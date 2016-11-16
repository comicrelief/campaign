(function($, Drupal) {
  $( document ).ready(function() {
    // IE fallback objectfit
    if(!Modernizr.objectfit) {
      $('.objectfit').each(function () {
        var $container = $(this),
            imgUrl = $container.find('img').prop('src');
        if (imgUrl) {
          $container
            .css('backgroundImage', 'url(' + imgUrl + ')')
            .css('background-size', 'cover')
            .addClass('compat-object-fit');
          $container.find('img').hide();
        }
      });
    }
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

  });
    // reload our styling for select boxes after ajax
  $( document ).ajaxComplete(function() {
    $('select').selectmenu({ style:'popup', width: '100%' });
  });

})(jQuery, Drupal);
