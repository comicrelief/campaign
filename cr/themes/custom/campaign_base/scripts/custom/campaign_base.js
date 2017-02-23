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

            $(".c-hamburger__text").text(function(i, text){
                return text === "More" ? "Close" : "More";
            });

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
        // Turn our boring select boxes into sexy jQuery UI selectboxes
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
