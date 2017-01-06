(function ($) {

	$(document).ready(function() {
		if ($( '.bg_video' ).length) {

	    var videoExists = false;
	    var breakpoint = window.matchMedia("(min-width: 1024px)");
	    var video;

			// Instatiates background video using vide library
			function backgroundVideo() {
				var videoArray = [];
				//get all video backgrounds 
				$( '.bg_video' ).each(function(index){
		      var path = $(this).data('vide-bg');
		      var options = $(this).data('vide-options');
		      //trim spaces from path
					path = path.trim();
					// initialization
				  videoArray[index] = $(this).vide(path, options);
				});
				return videoArray;
			}

			function breakpointMatches(mql) {

				if (mql.matches === true && !videoExists) {
	          //instatiate video when resizing to LG
	          video = backgroundVideo();
	          video = video[0];
	          videoExists = true;
	          $( '.promo-header__bg-image' ).hide();
	        }
	        if (mql.matches === false && videoExists) {
	          // remove video and show bg_image when resizing to SM again
			  		$( '.promo-header__bg-image' ).show();
			  		$( '.bg_video' ).hide();
	        }
	        else if(mql.matches === true && videoExists) {
	        	// hide bg_image and show video when resizing to LG again
				  	$( '.bg_video' ).show();
			  		$( '.promo-header__bg-image' ).hide();
				  }  
			}

	    // LG breakpoint on load
			if( breakpoint.matches ){
				// instatiate video and hide bg_image
	      video = backgroundVideo();
	      video = video[0];
	      videoExists = true;

	      $( '.promo-header__bg-image' ).hide();

	      // add listener to hide video if we resize below 1024
	      breakpoint.addListener(breakpointMatches);
			} 

	    // SM or MD breakpoint on load
	    else {
	      // add listener to instantiate/show video if we resize above 1024
	      breakpoint.addListener(breakpointMatches);
	    }
  	}
	});
})(jQuery);
