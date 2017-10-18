(function($, Drupal) {
  $( document ).ready(function() {

    // todo make a function
    $("a[role=button].meta-icons__magnify").on("click", function(e) {
      e.preventDefault();
      $(this).toggleClass("active");
      $(".search-block, .search-overlay:not('.show'), .search-overlay.search-on").toggleClass("show");
      $(".search-overlay").toggleClass("search-on");
      $(".search-overlay").removeClass("nav-on");
      $("header[role='banner'] nav, .block--cr-email-signup--head").removeClass("show");
      $("a[role=button].meta-icons__esu-toggle").removeClass("active");
      $(".search-block__form input[type=text]").focus();
    });

    $("a[role=button].c-hamburger").on("click", function() {
      $("header[role='banner'] nav, .search-overlay:not('.show'), .search-overlay.show.nav-on").toggleClass("show");
      $(".search-overlay").toggleClass("nav-on");
      $(".search-overlay").removeClass("search-on");
      $("a[role=button].meta-icons__esu-toggle, .meta-icons__magnify").removeClass("active");
      $(".block--cr-email-signup--head, .search-block").removeClass("show");
    });

    $("a[role=button].meta-icons__esu-toggle").on("click", function() {
      $("a[role=button].meta-icons__magnify").removeClass("active");
      $(".search-block, header[role='banner'] nav, .search-overlay").removeClass("show");
    });

    $(".search-block .close-button").on("click", function(e) {
      e.preventDefault();
      $("a[role=button].meta-icons__magnify").removeClass("active");
      $(".search-block, .search-overlay").removeClass("show");
    });

    // Close any active navs when we're toggling on other buttons in the nav
    $('.meta-icons a[role=button]').on('click', function (e) {
      // Remove active class from hamburger nav to collapse it
      $('a[role=button].c-hamburger.is-active').removeClass('is-active');
      $('.main-nav__items').removeClass('menu-open');
    });

    // Close all overlays and dropdowns when we're clicking on other content
    $(document).on('click touchstart', function (e) {
      
      // Use our custom body class to only run this UI function where we can
      $( ".crNavTooltips .has-tooltip" ).tooltip( "close" );

      // Check that we're not interacting with the nav; dont want to close anything being used
      if (!$(e.target).is('.meta-icons *, .feature-nav__icons *, .main-nav__icons *, .search-block *, ul.menu *, .block--cr-email-signup--head *')) {

        // Remove all active state classes from all of our active nav dropdowns
        $('a[role=button].c-hamburger.is-active').removeClass('is-active');
        $('.main-nav__items').removeClass('menu-open');
        $('.header__inner-wrapper nav.navigation.show, .search-block.show, .search-overlay.show, .block--cr-email-signup--head').removeClass('show');
        $('.meta-icons__esu-toggle.active, .meta-icons__magnify.active').removeClass('active');
        $('.search-overlay.search-on').removeClass('search-on');
      } 
    });
    
    $(".site-logo").attr('tabindex', 2);

    // IE fallback objectfit
    if(!Modernizr.objectfit) {
      
      $('.objectfit').each(function (index) {

        // Cache objectfit object and child image
        var $container = $(this);
        
        var imgUrl = $container.find('img').prop('src');
        var blazySrc = $container.find('img').attr('data-src');

        alert('oeloe');

        // Only if we've successfully found an image data-src(blazy) OR srcset
        if (imgUrl || blazySrc) {

          var bgImgUrl = blazySrc ? blazySrc : imgUrl;

          $container
            .css('backgroundImage', 'url(' + bgImgUrl + ')')
              .css('background-size', 'cover')
                .addClass('compat-object-fit')
                .find('.media--blazy').removeClass('media--loading');
          
          $container.find('img, picture').hide();
          $container.height('500px');
        }
      }

    // use jQuery UI selectboxes
    $('select').selectmenu();

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

    // jQuery UI tooltip instantiation for nav, only on non-touch devices
    $( ".no-touchevents .meta-icons .has-tooltip" ).tooltip({
      classes: { "ui-tooltip": "highlight"},
      tooltipClass: "ui-tooltip--nav",
      position: { my: "top", at: "bottom" },
      show: {effect: 'fadeIn', duration: 200},
      hide: {effect: 'fadeOut', duration: 0},
      create: function( event, ui ) {
        $('body').addClass('crNavTooltips');
      },
    });

    // Helper snippet as the cookie banner module doesn't provide a 'state' we can use for any affected styling
    setTimeout(function(){
     // Add our active class if the banner is present
      $('body > .cc_banner-wrapper').length ? $('body').addClass('cc-banner--visible') : null ;
      // Add a button click handler (if it's present in the DOM) to remove the active class
      $('.cc_banner-wrapper a.btn').on('click', function(){
        $('body').removeClass('cc-banner--visible');
      });
    }, 1500);
  })
})(jQuery, Drupal);
