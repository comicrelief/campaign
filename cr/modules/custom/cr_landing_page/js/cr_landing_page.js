(function ($) {
	$(document).ready(function () {
		//Background video using vide library
		//get all video backgrounds
		$( '.bg_video' ).each(function( index ) {
		  var path = $(this).data('vide-bg');
		  var options = $(this).data('vide-options');
		  //trim spaces from path
		  path = path.trim();
		  console.log(path);
		  // initialization
		  $(this).vide(path, options);
		});
	});
})(jQuery);
