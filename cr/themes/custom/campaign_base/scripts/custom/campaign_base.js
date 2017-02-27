(function($, Drupal) {
  $( document ).ready(function() {

    // todo make a function
    $("button.meta-icons__magnify").on("click", function() {
      $(this).toggleClass("active");
      $(".search-block, .search-overlay:not('.show'), .search-overlay.search-on").toggleClass("show");
      $(".search-overlay").toggleClass("search-on");
      $(".search-overlay").removeClass("nav-on");
      $("header[role='banner'] nav, .block--cr-email-signup--head").removeClass("show");
      $("button.meta-icons__esu-toggle").removeClass("active");
      $(".search-block__form input[type=text]").focus();
    });

    $("button.feature-nav-toggle").on("click", function() {
      $(this).toggleClass("is-active");
      $("header[role='banner'] nav, .search-overlay:not('.show'), .search-overlay.show.nav-on").toggleClass("show");
      $(".search-overlay").toggleClass("nav-on");
      $(".search-overlay").removeClass("search-on");
      $("button.meta-icons__esu-toggle, .meta-icons__magnify").removeClass("active");
      $(".block--cr-email-signup--head, .search-block").removeClass("show");
    });

    $("button.meta-icons__esu-toggle").on("click", function() {
      $("button.meta-icons__magnify").removeClass("active");
      $(".search-block, header[role='banner'] nav, .search-overlay").removeClass("show");
    });

    $(".search-block .icon").on("click", function() {
      $("button.meta-icons__magnify").removeClass("active");
      $(".search-block, .search-overlay").removeClass("show");
    });

    // Close any active navs when we're toggling on other buttons in the nav
    $('.meta-icons button').on('click', function (e) {
      // Remove active class from hamburger nav to collapse it
      $('button.feature-nav-toggle.is-active').removeClass('is-active');
    });

    // Close all overlays and dropdowns when we're clicking on other content
    $(document).on('click touchstart', function (e) {

      // Check that we're not interacting with the nav; dont want to close anything being used
      if (!$(e.target).is('.meta-icons *, .feature-nav__icons *, .search-block *, ul.menu *, .block--cr-email-signup--head *')) {

        // Remove all active state classes from all of our active nav dropdowns
        $('button.feature-nav-toggle.is-active').removeClass('is-active');
        $('.header__inner-wrapper nav.navigation.show, .search-overlay.show, .block--cr-email-signup--head').removeClass('show');
        $('.meta-icons__esu-toggle.active, meta-icons__magnify.active').removeClass('active');
        $('.search-overlay.search-on').removeClass('search-on');
      }
    });
    
    $(".site-logo").attr('tabindex', 2);

    // IE fallback objectfit
    if(!Modernizr.objectfit) {
      
      $('.objectfit').each(function (index) {

        // Cache objectfit object and child image
        var $container = $(this);
        var $thisImage = $container.find('img');

        var imgSrc = $thisImage.attr('src');
        var imgSrcSet = $thisImage.attr('srcset');

        // Only if we've successfully found an image src OR srcset
        if (imgSrc || imgSrcSet) {

          var imgUrl = imgSrc ? imgSrc : imgSrcSet;

          $container
            .css('backgroundImage', 'url(' + imgUrl + ')')
              .css('background-size', 'cover')
                .addClass('compat-object-fit');
          
          $container.find('img').hide();

        }
      });
    }

        // use jQuery UI selectboxes
        $('select').selectmenu();
        // Activate lighcase
        // Video lightcase
        $('a[data-rel^=lightcase]').lightcase({
            overlayOpacity: .95,
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

        // ui selectmenu change listener for
        // news landing page exposed filter
        selectMenuChange();
        function selectMenuChange() {
            $('select').selectmenu({
                change: function(event, ui) {
                    //click on form's hidden submit button to trigger Ajax call
                    $(this).parents('form').find('.form-submit').click();
                }
            });
        }

        $(document).ajaxComplete(function() {
            selectMenuChange();
        });

    })
})(jQuery, Drupal);
